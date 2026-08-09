<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\TransitFiles;
use App\Models\TransitFolderItems;
use App\Models\TransitFolders as ModelsTransitFolders;
use CodeIgniter\Exceptions\PageNotFoundException;

class TransitFolders extends BaseController
{
    public function index()
    {
        $model = new ModelsTransitFolders();

        $r = $this->request->getGet('r');
        if ($r) {
            $model
                ->like("id", $r);
        }

        $folders = $model->find();
        return view("transit_folders/index", [
            "folders" => $folders
        ]);
    }

    public function addPage()
    {
        $modelClient = new Clients();
        return view("transit_folders/add", [
            "clients" => $modelClient->orderBy("name", 'asc')->findAll()
        ]);
    }

    public function add()
    {
        $data = $this->request->getPost();
        $model = new ModelsTransitFolders();

        //id generation
        if (empty($data["id"])) {
            $newId = $this->generateId();
            $data["id"] = $newId;
        }

        try {
            $model->insert($data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->with("message", "Création du dossier Nº" . $data["id"] . " réussie.")
            ->to('dossiers/modifier/' . $data["id"]);
    }

    // Fonction pour générer un nouvel identifiant from Chat GPT
    private function generateId()
    {
        $model = new ModelsTransitFolders();

        // Récupérer le dernier enregistrement de la base de données
        $lastRecord = $model->orderBy('id', 'DESC')->first();
        // Si aucun enregistrement n'existe encore
        if (!$lastRecord) {
            // Générer un nouvel identifiant pour le premier enregistrement
            $year = date('Y');
            $month = date('m');
            $newId = $year . $month . '00001'; // Ou plus, selon votre choix
        } else {
            // Récupérer les parties de l'identifiant
            $lastId = $lastRecord['id'];
            $year = substr($lastId, 0, 4);
            $month = substr($lastId, 4, 2);
            $number = substr($lastId, 6);

            // Vérifier si le mois actuel est différent du mois de l'identifiant le plus récent
            if ($month != date('m')) {
                // Si le mois est différent, commencer un nouveau compteur à partir de 1
                $month = date('m');
                $number = '00001'; // Ou plus, selon votre choix
            } else {
                // Sinon, incrémenter le compteur actuel
                $number++;
                // Vous pouvez ajouter une logique pour ajuster la longueur du nombre en fonction de sa longueur actuelle
            }

            // Construire le nouvel identifiant
            $newId = $year . $month . sprintf("%05d", $number);
        }

        return $newId;
    }

    public function editPage($id)
    {
        $model = new ModelsTransitFolders();
        $folder = $model->find($id);
        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }
        $folder["clients"] = (new Clients())->orderBy("name", "asc")->findAll();
        return view("transit_folders/edit", $folder);
    }

    public function edit()
    {
        $model = new ModelsTransitFolders();
        $data = $this->request->getPost();
        try {
            $model->save($data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage())
                ->withInput();
        }
        return redirect()
            ->back()
            ->with('message', 'Modifications enregistrées.');
    }

    public function addItem()
    {
        $data = $this->request->getPost();
        $model = new TransitFolderItems();
        try {
            $model->save($data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with('message', "Enregistrement réussie.");
    }

    public function deleteItem()
    {
        $data = $this->request->getPost();
        $model = new TransitFolderItems();
        try {
            $model->delete($data["id"]);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }
        return redirect()
            ->back()
            ->with("message", "Suppression réussie");
    }

    public function info($id)
    {
        $model = new ModelsTransitFolders();
        $folder = $model->find($id);

        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("transit_folders/info", $folder);
    }

    public function print($id)
    {
        $model = new ModelsTransitFolders();
        $folder = $model->find($id);

        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("transit_folders/print", $folder);
    }

    public function deletePage($id)
    {
        $model = new ModelsTransitFolders();
        $folder = $model->find($id);

        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("transit_folders/delete", $folder);
    }

    public function delete($id)
    {
        $data = $this->request->getPost();

        if ($id != $data["id"]) {
            return redirect()
                ->back()
                ->with("error", "Echec de la confirmation de suppression.");
        }

        $model = new ModelsTransitFolders();
        try {
            $model->delete($id);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->to("/dossiers")
            ->with("message", 'Suppression du dossier <code>' . $id . '</code> réussie!');
    }

    public function getCriticalFolders()
    {
        $model = new ModelsTransitFolders();
        $currentDateMinus3Days = date('Y-m-d', strtotime('-3 days'));
        $criticals = $model
            ->where("closed", false)
            ->where("open_date <", $currentDateMinus3Days)
            ->find();
        return $criticals;
    }

    public function addFile()
    {

        $file = $this->request->getFile("file");
        $data = $this->request->getPost();

        if (!$file or $file->getSize() == 0) {
            return redirect()
                ->back()
                ->with("error", "Aucun fichier joint.");
        }

        echo view("loading", [
            "name" => $data["name"],
            "size" => $file->getSizeByUnit('mb'),
        ]);

        try {
            $file->move(ROOTPATH . '/public/files_uploaded');
            $data["url"] = base_url("files_uploaded/" . $file->getName());
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        $model = new TransitFiles();
        $data["folder_id"] = intval($data["folder_id"]);
        try {
            $model->insert($data);
        } catch (\Throwable $th) {
            delete_files(ROOTPATH . '/public/files_uploaded' . $file->getName());
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("Message", "Fichier enregistré: " . $data["name"]);
    }

    public function deleteFile()
    {
        $data = $this->request->getPost();
        $model = new TransitFiles();

        $file = $model->find($data["id"]);
        try {
            delete_files($file["url"]);
            $model->delete($data["id"]);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("Message", "Fichier supprimé: " . $file["name"]);
    }
}
