<?php

namespace App\Controllers;

use App\Models\Plans;
use App\Models\Tenants;
use App\Models\Users as ModelsUsers;
use Config\Tenancy;

/**
 * Inscription en libre-service d'une nouvelle agence.
 *
 * Crée l'agence puis son premier administrateur, dans une transaction: une
 * agence sans aucun compte serait inaccessible et invisible, donc impossible
 * à rattraper autrement qu'en base.
 */
class Signup extends BaseController
{
    public function form(): string
    {
        return view("signup", [
            "plan" => (new Plans())->default(),
        ]);
    }

    public function create()
    {
        $data = $this->request->getPost();
        $slug = strtolower(trim((string) ($data["slug"] ?? "")));

        $erreur = $this->validateSubmission($data, $slug);
        if ($erreur !== null) {
            return redirect()->back()->withInput()->with("error", $erreur);
        }

        $tenants = new Tenants();
        $plan = (new Plans())->default();

        $db = db_connect();
        $db->transStart();

        $tenantId = $tenants->insert([
            "name" => trim((string) $data["name"]),
            "slug" => $slug,
            "active" => 1,
            "plan_id" => $plan["id"] ?? null,
        ], true);

        if (!$tenantId) {
            $db->transRollback();

            return redirect()
                ->back()
                ->withInput()
                ->with("error", implode(" ", $tenants->errors()) ?: "Création de l'agence impossible.");
        }

        // Le contexte doit pointer sur la nouvelle agence avant d'écrire le
        // compte: le modèle Users est cloisonné et estampille tenant_id
        // depuis le contexte, pas depuis le POST.
        tenant()->set((int) $tenantId);

        $users = new ModelsUsers();
        $created = $users->insert([
            "name" => trim((string) $data["admin_name"]),
            "email" => trim((string) $data["admin_email"]),
            "password" => (string) $data["admin_password"],
            "profile" => "ADMIN",
        ]);

        if (!$created) {
            $db->transRollback();

            return redirect()
                ->back()
                ->withInput()
                ->with("error", implode(" ", $users->errors()) ?: "Création du compte administrateur impossible.");
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "L'inscription a échoué, aucune donnée n'a été enregistrée.");
        }

        return redirect()
            ->to($this->tenantUrl($slug))
            ->with("message", "Agence créée. Connectez-vous avec le compte administrateur.");
    }

    /** Renvoie le message d'erreur, ou null si la saisie est acceptable. */
    private function validateSubmission(array $data, string $slug): ?string
    {
        foreach (["name", "slug", "admin_name", "admin_email", "admin_password"] as $champ) {
            if (empty($data[$champ])) {
                return "Tous les champs sont obligatoires.";
            }
        }

        if (($data["admin_password"] ?? "") !== ($data["admin_password_confirm"] ?? "")) {
            return "La confirmation du mot de passe ne correspond pas.";
        }

        if (strlen((string) $data["admin_password"]) < 8) {
            return "Le mot de passe doit faire au moins 8 caractères.";
        }

        if (!filter_var($data["admin_email"], FILTER_VALIDATE_EMAIL)) {
            return "Adresse email invalide.";
        }

        if (preg_match("/^[a-z0-9][a-z0-9-]{1,49}$/", $slug) !== 1) {
            return "L'identifiant d'agence doit faire 2 à 50 caractères, en minuscules, chiffres ou tirets.";
        }

        if (in_array($slug, config(Tenancy::class)->reservedSlugs, true)) {
            return "Cet identifiant d'agence est réservé.";
        }

        // Les agences supprimées gardent leur slug: on ne recycle pas une
        // adresse qui a pu être diffusée à des clients.
        $existe = db_connect()->table("tenants")->where("slug", $slug)->countAllResults();
        if ($existe > 0) {
            return "Cet identifiant d'agence est déjà pris.";
        }

        return null;
    }

    private function tenantUrl(string $slug): string
    {
        $config = config(Tenancy::class);
        $host = $_SERVER["HTTP_HOST"] ?? $config->baseDomain;
        $parts = explode(":", $host);
        $port = isset($parts[1]) ? ":" . $parts[1] : "";
        $scheme = $this->request->isSecure() ? "https" : "http";

        return $scheme . "://" . $slug . "." . $config->baseDomain . $port . "/";
    }
}
