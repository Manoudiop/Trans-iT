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

        // save() se comporte en upsert quand useAutoIncrement est désactivé:
        // il décide d'insérer ou de modifier selon l'existence de la ligne.
        // Un POST portant un numéro inconnu créait donc un dossier au lieu
        // d'échouer. Une modification ne doit modifier que de l'existant.
        if (empty($data["id"]) or !$model->find($data["id"])) {
            throw new PageNotFoundException("Dossier Nº" . ($data["id"] ?? "") . " introuvable.");
        }

        try {
            $model->update($data["id"], $data);
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

        if (!$file or !$file->isValid() or $file->getSize() == 0) {
            return redirect()
                ->back()
                ->with("error", "Aucun fichier joint.");
        }

        $folderId = intval($data["folder_id"]);
        if (!(new ModelsTransitFolders())->find($folderId)) {
            throw new PageNotFoundException("Dossier Nº" . $folderId . " introuvable.");
        }

        // Nom généré: le nom d'origine vient du client et ne doit jamais
        // atterrir tel quel sur le disque (collisions, traversée de chemin).
        // Le chemin est préfixé par l'agence car le numéro de dossier n'est
        // unique que par agence: sans ce préfixe, deux agences se
        // marcheraient dessus sur le disque.
        $storedName = $file->getRandomName();
        $relative = tenant_id() . "/" . $folderId . "/" . $storedName;

        try {
            $file->move(dirname(WRITEPATH . "uploads/" . $relative), $storedName);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        $model = new TransitFiles();
        try {
            $model->insert([
                "folder_id" => $folderId,
                "name" => $data["name"],
                "path" => $relative,
            ]);
        } catch (\Throwable $th) {
            @unlink(WRITEPATH . "uploads/" . $relative);
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", "Fichier enregistré: " . $data["name"]);
    }

    /**
     * Sert une pièce jointe depuis le stockage privé. La route est derrière le
     * filtre d'authentification: plus aucun document n'est accessible par URL
     * devinée comme c'était le cas sous public/files_uploaded.
     */
    public function file($id)
    {
        $file = (new TransitFiles())->find($id);

        if (!$file) {
            throw new PageNotFoundException("Fichier introuvable.");
        }

        $absolute = $this->resolveFilePath($file);
        if (!$absolute) {
            throw new PageNotFoundException("Fichier introuvable.");
        }

        // Le nom affiché vient de la base: on neutralise ce qui pourrait
        // casser l'en-tête Content-Disposition.
        $name = str_replace(["\"", "\r", "\n"], "", (string) $file["name"]);

        // Un document affiché en inline s'exécute dans l'origine de
        // l'application: seuls les formats inoffensifs y ont droit, le reste
        // part en téléchargement avec un type neutre.
        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $inlineSafe = in_array($extension, ["pdf", "png", "jpg", "jpeg", "gif", "webp", "txt"], true);

        return $this->response
            ->setHeader("Content-Type", $inlineSafe ? $this->guessMimeType($absolute) : "application/octet-stream")
            ->setHeader("Content-Disposition", ($inlineSafe ? "inline" : "attachment") . '; filename="' . $name . '"')
            ->setHeader("Content-Length", (string) filesize($absolute))
            ->setHeader("X-Content-Type-Options", "nosniff")
            ->setBody(file_get_contents($absolute));
    }

    public function deleteFile()
    {
        $data = $this->request->getPost();
        $model = new TransitFiles();

        $file = $model->find($data["id"]);
        if (!$file) {
            throw new PageNotFoundException("Fichier introuvable.");
        }

        // Résolu avant la suppression en base, sinon on perd le chemin.
        $absolute = $this->resolveFilePath($file);

        try {
            $model->delete($data["id"]);
            if ($absolute) {
                unlink($absolute);
            }
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", "Fichier supprimé: " . $file["name"]);
    }

    /**
     * Chemin absolu d'une pièce jointe, ou null si elle est introuvable ou
     * pointe hors du stockage autorisé.
     */
    private function resolveFilePath(array $file): ?string
    {
        if (!empty($file["path"])) {
            $root = realpath(WRITEPATH . "uploads");
            $absolute = realpath(WRITEPATH . "uploads/" . $file["path"]);

            if (!$root or !$absolute or !str_starts_with($absolute, $root)) {
                return null;
            }

            return is_file($absolute) ? $absolute : null;
        }

        // Pièces jointes antérieures à la migration, encore sous public/.
        if (!empty($file["url"])) {
            $legacy = realpath(ROOTPATH . "public/files_uploaded/" . basename($file["url"]));
            return ($legacy and is_file($legacy)) ? $legacy : null;
        }

        return null;
    }

    private function guessMimeType(string $absolute): string
    {
        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));
        $mime = \Config\Mimes::guessTypeFromExtension($extension);

        return $mime ?: "application/octet-stream";
    }
}
