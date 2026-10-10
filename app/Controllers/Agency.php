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

    /** Un champ facultatif laissé vide vaut NULL, pas la chaîne vide. */
    private function normalize(?string $valeur): ?string
    {
        $valeur = trim((string) $valeur);

        return $valeur === "" ? null : $valeur;
    }
}
