<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients as ModelsClients;
use CodeIgniter\Exceptions\PageNotFoundException;

class Clients extends BaseController
{
    public function index()
    {
        $model = new ModelsClients();
        $req = $this->request->getGet();
        return view("clients/index", [
            "clients" => $model
                ->orderBy("name")
                ->find($req ? $req : null)
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
