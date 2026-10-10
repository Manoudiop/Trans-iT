<?php

namespace App\Controllers;

use App\Models\Tenants;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Paramètres de l'agence connectée.
 *
 * Ce qui est saisi ici part sur les documents remis aux clients: l'en-tête
 * de la facture notamment. Une agence ne voit et ne modifie que la sienne,
 * l'identifiant venant du contexte, jamais d'un champ de formulaire.
 */
class Agency extends BaseController
{
    /** Un logo d'en-tête reste léger: au-delà, c'est une photo déposée par erreur. */
    private const LOGO_MAX_BYTES = 512000;

    /** Formats affichables sans exécuter de code, indexés par constante IMAGETYPE_*. */
    private const FORMATS = [
        IMAGETYPE_PNG => "png",
        IMAGETYPE_JPEG => "jpg",
        IMAGETYPE_GIF => "gif",
        IMAGETYPE_WEBP => "webp",
    ];

    public function profile()
    {
        $agence = (new Tenants())->find(tenant_id());

        if (!$agence) {
            throw new PageNotFoundException("Agence introuvable.");
        }

        return view("agency/profile", ["agence" => $agence]);
    }

    public function save()
    {
        $modele = new Tenants();

        // Les champs modifiables sont énumérés: le slug porte le
        // sous-domaine et le plan porte l'abonnement, ni l'un ni l'autre ne
        // se change depuis le profil.
        $data = [
            "name" => trim((string) $this->request->getPost("name")),
            "address" => $this->normalize($this->request->getPost("address")),
            "phone" => $this->normalize($this->request->getPost("phone")),
            "ninea" => $this->normalize($this->request->getPost("ninea")),
            "agreement_number" => $this->normalize($this->request->getPost("agreement_number")),
        ];

        if (!$modele->update(tenant_id(), $data)) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", implode(" ", $modele->errors()));
        }

        return redirect()
            ->to("agence")
            ->with("message", "Paramètres de l'agence enregistrés.");
    }

    /**
     * Dépose le logo de l'agence.
     *
     * Les formats acceptés sont ceux qu'un navigateur affiche sans exécuter
     * de code. Le SVG en est volontairement exclu: il peut porter du script,
     * et il serait servi depuis l'origine de l'application.
     */
    public function uploadLogo()
    {
        $fichier = $this->request->getFile("logo");

        if (!$fichier or !$fichier->isValid() or $fichier->getSize() === 0) {
            return redirect()->back()->with("error", "Aucun fichier reçu.");
        }

        if ($fichier->getSize() > self::LOGO_MAX_BYTES) {
            return redirect()->back()->with(
                "error",
                "Logo trop lourd: " . round(self::LOGO_MAX_BYTES / 1024) . " Ko au maximum."
            );
        }

        // Le type est déduit du contenu, pas de l'extension envoyée par le
        // navigateur: une extension se renomme, les octets non.
        $image = @getimagesize($fichier->getTempName());
        $extension = self::FORMATS[$image[2] ?? 0] ?? null;

        if ($extension === null) {
            return redirect()->back()->with(
                "error",
                "Format non reconnu: déposez une image PNG, JPEG, GIF ou WebP."
            );
        }

        $agence = (new Tenants())->find(tenant_id());
        $ancien = $agence["logo_path"] ?? null;

        // Nom aléatoire: le fichier est servi par une route, son nom sur le
        // disque n'a pas à être devinable.
        $nom = bin2hex(random_bytes(8)) . "." . $extension;
        $relatif = tenant_id() . "/agence/" . $nom;

        try {
            $fichier->move(dirname(WRITEPATH . "uploads/" . $relatif), $nom);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        if (!(new Tenants())->update(tenant_id(), ["logo_path" => $relatif])) {
            @unlink(WRITEPATH . "uploads/" . $relatif);

            return redirect()->back()->with("error", "Enregistrement du logo impossible.");
        }

        // L'ancien fichier n'est retiré qu'une fois le nouveau enregistré:
        // un échec en cours de route laisse l'agence avec son logo actuel.
        $this->removeFile($ancien);

        return redirect()->to("agence")->with("message", "Logo enregistré.");
    }

    public function deleteLogo()
    {
        $agence = (new Tenants())->find(tenant_id());

        if (empty($agence["logo_path"])) {
            return redirect()->to("agence")->with("error", "Aucun logo à retirer.");
        }

        (new Tenants())->update(tenant_id(), ["logo_path" => null]);
        $this->removeFile($agence["logo_path"]);

        return redirect()->to("agence")->with("message", "Logo retiré.");
    }

    /**
     * Sert le logo de l'agence connectée.
     *
     * Le chemin vient de la base, jamais de l'URL: il n'y a rien à deviner,
     * et une agence ne peut pas demander le logo d'une autre.
     */
    public function logo()
    {
        $chemin = agency()["logo_path"] ?? null;
        $absolu = $chemin === null ? null : realpath(WRITEPATH . "uploads/" . $chemin);
        $racine = realpath(WRITEPATH . "uploads");

        if (!$absolu or !$racine or !str_starts_with($absolu, $racine) or !is_file($absolu)) {
            throw new PageNotFoundException("Aucun logo.");
        }

        $image = @getimagesize($absolu);

        if (!isset(self::FORMATS[$image[2] ?? 0])) {
            throw new PageNotFoundException("Aucun logo.");
        }

        return $this->response
            ->setHeader("Content-Type", $image["mime"])
            ->setHeader("Content-Length", (string) filesize($absolu))
            ->setHeader("X-Content-Type-Options", "nosniff")
            ->setHeader("Cache-Control", "private, max-age=300")
            ->setBody(file_get_contents($absolu));
    }

    /** Un champ facultatif laissé vide vaut NULL, pas la chaîne vide. */
    private function normalize(?string $valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return $valeur === "" ? null : $valeur;
    }

    /** Efface un fichier de logo en restant sous la racine de stockage. */
    private function removeFile(?string $chemin): void
    {
        if (empty($chemin)) {
            return;
        }

        $absolu = realpath(WRITEPATH . "uploads/" . $chemin);
        $racine = realpath(WRITEPATH . "uploads");

        if ($absolu and $racine and str_starts_with($absolu, $racine) and is_file($absolu)) {
            @unlink($absolu);
        }
    }
}
