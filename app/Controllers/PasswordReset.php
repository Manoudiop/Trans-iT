<?php

namespace App\Controllers;

use App\Libraries\Mailer;
use App\Models\PasswordResets;
use App\Models\Tenants;
use App\Models\Users as ModelsUsers;

/**
 * Mot de passe oublié.
 *
 * Deux temps: on demande l'email et l'on reçoit un lien, puis le lien
 * ouvre le formulaire de nouveau mot de passe.
 *
 * La réponse à la demande est toujours la même, que le compte existe ou
 * non. Autrement le formulaire dirait qui est client du service, et chez
 * un transitaire cela revient à désigner les concurrents d'une place.
 */
class PasswordReset extends BaseController
{
    public function askForm(): string
    {
        return view("password/ask");
    }

    public function ask()
    {
        $email = trim((string) $this->request->getPost("email"));
        $neutre = "Si un compte existe avec cette adresse, un lien de réinitialisation vient d'y être envoyé. "
            . "Il est valable " . PasswordResets::DUREE_MINUTES . " minutes.";

        $user = $this->resoudreCompte($email);

        if ($user === null) {
            return redirect()->to("mot-de-passe-oublie")->with("message", $neutre);
        }

        $jetons = new PasswordResets();

        // Le frein ne se voit pas du dehors: la réponse reste la même, seul
        // le second courriel n'est pas envoyé.
        if ($jetons->demandeTropRecente((int) $user["id"])) {
            return redirect()->to("mot-de-passe-oublie")->with("message", $neutre);
        }

        $agence = (new Tenants())->find($user["tenant_id"]);
        $jeton = $jetons->emettre((int) $user["tenant_id"], (int) $user["id"]);

        [$envoye, $erreur] = (new Mailer())->send(
            $email,
            "Réinitialisation de votre mot de passe",
            $this->corpsDuMessage($user, $agence, $jeton)
        );

        if (!$envoye) {
            // Un lien émis mais jamais parti ne doit pas rester utilisable.
            $ligne = $jetons->valide($jeton);
            if ($ligne !== null) {
                $jetons->consommer((int) $ligne["id"]);
            }

            return redirect()->to("mot-de-passe-oublie")->with("error", $erreur);
        }

        return redirect()->to("mot-de-passe-oublie")->with("message", $neutre);
    }

    public function resetForm(string $jeton)
    {
        if ((new PasswordResets())->valide($jeton) === null) {
            return redirect()
                ->to("mot-de-passe-oublie")
                ->with("error", "Ce lien a expiré ou a déjà servi. Demandez-en un nouveau.");
        }

        return view("password/reset", ["jeton" => $jeton]);
    }

    public function update()
    {
        $jeton = (string) $this->request->getPost("jeton");
        $motdepasse = (string) $this->request->getPost("password");
        $confirmation = (string) $this->request->getPost("password_confirm");

        $jetons = new PasswordResets();
        $ligne = $jetons->valide($jeton);

        if ($ligne === null) {
            return redirect()
                ->to("mot-de-passe-oublie")
                ->with("error", "Ce lien a expiré ou a déjà servi. Demandez-en un nouveau.");
        }

        if (strlen($motdepasse) < 8) {
            return redirect()->back()->with("error", "Le mot de passe doit faire au moins 8 caractères.");
        }

        if ($motdepasse !== $confirmation) {
            return redirect()->back()->with("error", "La confirmation ne correspond pas.");
        }

        // Le modèle Users est cloisonné: l'agence vient de la ligne de jeton,
        // jamais de la session ni de l'hôte.
        tenant()->set((int) $ligne["tenant_id"]);

        $users = new ModelsUsers();

        if (!$users->update($ligne["user_id"], ["password" => $motdepasse])) {
            return redirect()->back()->with("error", implode(" ", $users->errors()) ?: "Enregistrement impossible.");
        }

        // Consommé après l'écriture: si l'enregistrement échoue, le lien
        // reste utilisable plutôt que de laisser le compte sans issue.
        $jetons->consommer((int) $ligne["id"]);

        return redirect()
            ->to("/")
            ->with("message", "Mot de passe modifié. Connectez-vous avec le nouveau.");
    }

    /**
     * Le compte visé, ou null si l'adresse n'en désigne pas exactement un.
     *
     * Même règle qu'à la connexion: le sous-domaine lève l'ambiguïté d'un
     * email partagé par plusieurs agences. Sans lui et avec plusieurs
     * candidats, on ne peut pas deviner lequel, et deviner reviendrait à
     * envoyer un lien de réinitialisation au mauvais destinataire.
     *
     * @return array<string, mixed>|null
     */
    private function resoudreCompte(string $email): ?array
    {
        if ($email === "" or !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $candidats = (new ModelsUsers())->findAllForLogin($email);

        $viaHote = tenant()->tenantFromHost();
        if ($viaHote !== null) {
            $candidats = array_values(array_filter(
                $candidats,
                static fn (array $u): bool => (int) $u["tenant_id"] === (int) $viaHote["id"]
            ));
        }

        return count($candidats) === 1 ? $candidats[0] : null;
    }

    /**
     * @param array<string, mixed>      $user
     * @param array<string, mixed>|null $agence
     */
    private function corpsDuMessage(array $user, ?array $agence, string $jeton): string
    {
        // Le lien porte le sous-domaine de l'agence: c'est l'adresse par
        // laquelle son équipe entre, et celle sur laquelle elle se
        // reconnectera ensuite.
        $lien = $this->lienAgence($agence["slug"] ?? null) . "reinitialiser/" . $jeton;

        return "Bonjour " . ($user["name"] ?? "") . ",\n\n"
            . "Vous avez demandé à réinitialiser le mot de passe de votre compte "
            . ($agence["name"] ?? "Trans It!") . ".\n\n"
            . "Ouvrez ce lien pour choisir un nouveau mot de passe:\n"
            . $lien . "\n\n"
            . "Ce lien est valable " . PasswordResets::DUREE_MINUTES . " minutes et ne sert qu'une fois.\n\n"
            . "Si vous n'êtes pas à l'origine de cette demande, ignorez ce message: "
            . "votre mot de passe actuel reste valable.\n";
    }

    private function lienAgence(?string $slug): string
    {
        if ($slug === null) {
            return base_url("/");
        }

        $config = config(\Config\Tenancy::class);
        $hote = $_SERVER["HTTP_HOST"] ?? $config->baseDomain;
        $parts = explode(":", $hote);
        $port = isset($parts[1]) ? ":" . $parts[1] : "";
        $scheme = $this->request->isSecure() ? "https" : "http";

        return $scheme . "://" . $slug . "." . $config->baseDomain . $port . "/";
    }
}
