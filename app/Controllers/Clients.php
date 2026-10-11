<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\ListOptions;
use App\Models\Clients as ModelsClients;
use CodeIgniter\Exceptions\PageNotFoundException;

class Clients extends BaseController
{
    /** Colonnes sur lesquelles la liste des clients accepte d'être triée. */
    private const TRIS = [
        "nom" => "name",
        "compte" => "account_number",
        "creation" => "created_at",
    ];

    public function index()
    {
        $model = new ModelsClients();
        $options = new ListOptions($this->request, self::TRIS, "nom", "ASC");

        if ($options->search !== "") {
            // La recherche se faisait par find($_GET), c'est-à-dire une
            // égalité stricte sur l'identifiant: pratique pour les liens
            // « clients?r=<id> » posés par les listes de dossiers, inutile
            // pour quelqu'un qui tape un nom. Les deux cas sont désormais
            // couverts, et le groupe est parenthésé pour que le OR ne
            // s'affranchisse pas du cloisonnement par agence.
            $model->groupStart()
                ->like("id", $options->search)
                ->orLike("name", $options->search)
                ->orLike("account_number", $options->search)
                ->orLike("email", $options->search)
                ->orLike("phone", $options->search)
                ->groupEnd();
        }

        $clients = $model
            ->orderBy($options->column(), $options->dir)
            ->orderBy("id", "ASC")
            ->paginate($options->perPage);

        return view("clients/index", [
            "clients" => $clients,
            "pager" => $model->pager,
            "options" => $options,
        ]);
    }

    public function delete()
    {
        $model = new ModelsClients();
        $data = $this->request->getPost();

        try {
            $model->delete($data["id"]);
        } catch (\Throwable $th) {
            // La clé étrangère composite refuse désormais la suppression d'un
            // client encore rattaché à des dossiers, au lieu de détacher
            // silencieusement des pièces comptables.
            $message = str_contains($th->getMessage(), "foreign key")
                ? "Ce client ne peut pas être supprimé: des dossiers de transit lui sont rattachés."
                : $th->getMessage();

            return redirect()
                ->back()
                ->with("error", $message);
        }

        return redirect()
            ->back()
            ->with("message", "Suppression réussie.");
    }

    public function editPage($id)
    {
        $model = new ModelsClients();
        $client = $model->find($id);
        if (!$client) {
            throw new PageNotFoundException("Compte client introuvable");
        }
        return view("clients/edit", $client);
    }

    public function save()
    {
        $modele = new ModelsClients();
        $data = $this->request->getPost();

        // Un identifiant facultatif laissé vide vaut NULL. Sinon la chaîne
        // vide s'enregistre, et « NINEA renseigné » devient indistinguable
        // de « NINEA absent » sur la facture.
        foreach (["ninea", "ppm"] as $facultatif) {
            if (isset($data[$facultatif])) {
                $data[$facultatif] = trim($data[$facultatif]) === "" ? null : trim($data[$facultatif]);
            }
        }

        if (isset($data["id"])) {

            //unique email validation
            if (isset($data["email"])) {
                $data["email"] = $data["email"] == "" ? null : $data["email"];
                if ($data["email"] != null) {
                    $email_count = $modele
                        ->select("id")
                        ->where("email", $data["email"])
                        ->find();
                    if (count($email_count) == 1 and $email_count[0]["id"] != $data["id"]) {
                        return redirect()
                            ->back()
                            ->withInput()
                            ->with("error", "Email en doublon.");
                    }
                }
            }

            //unique phone validation
            if (isset($data["phone"])) {
                $data["phone"] = $data["phone"] == "" ? null : $data["phone"];
                if ($data["phone"] != null) {
                    $phone_count = $modele
                        ->select("id")
                        ->where("phone", $data["phone"])
                        ->find();
                    if (count($phone_count) == 1 and $phone_count[0]["id"] != $data["id"]) {
                        return redirect()
                            ->back()
                            ->withInput()
                            ->with("error", "Numéro de téléphone en doublon.");
                    }
                }
            }

            $message = "Modifications enregistrées.";
        } else {
            $message = "Création du compte réussie.";
        }

        try {
            $modele->save($data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", $message);
    }

    public function addPage()
    {
        return view("clients/add");
    }
}
