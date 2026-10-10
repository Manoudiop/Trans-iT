<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\TransitFiles;
use App\Models\TransitFolderItems;
use App\Models\TransitFolders as ModelsTransitFolders;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Workflow;

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

        if (!quotas()->canAddFolder()) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Limite de " . quotas()->foldersPerMonthLimit() . " dossiers par mois atteinte pour votre offre.");
        }

        // Un dossier à la corbeille occupe toujours son connaissement: sans
        // ce contrôle, la ressaisie échouerait sur une erreur de doublon
        // incompréhensible pour l'utilisateur.
        if (!empty($data["bl"])) {
            $supprime = $model->deletedHolderOfBl((string) $data["bl"]);
            if ($supprime !== null) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with("error", "Le connaissement " . $data["bl"] . " appartient au dossier Nº"
                        . $supprime["id"] . ", qui est à la corbeille. Restaurez-le au lieu de le ressaisir.");
            }
        }

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

        $period = date("Ym");
        $db = db_connect();

        // Allocation atomique: LAST_INSERT_ID(expr) fixe la valeur de session
        // et la renvoie, dans la branche insertion comme dans la branche mise
        // à jour. La ligne (agence, mois) est verrouillée le temps de
        // l'écriture, donc deux créations simultanées obtiennent deux numéros
        // distincts — là où la lecture du maximum puis l'incrément laissaient
        // passer un doublon.
        //
        // tenant_id est passé explicitement: folder_sequences est une table
        // d'infrastructure, sans modèle cloisonné.
        $db->query(
            "INSERT INTO `folder_sequences` (`tenant_id`, `period`, `last_number`)"
            . " VALUES (?, ?, LAST_INSERT_ID(1))"
            . " ON DUPLICATE KEY UPDATE `last_number` = LAST_INSERT_ID(`last_number` + 1)",
            [tenant_id(), $period]
        );

        $next = (int) ($db->query("SELECT LAST_INSERT_ID() AS n")->getRowArray()["n"] ?? 1);

        return $period . sprintf("%05d", $next);
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
            // Sans balises: les messages flash sont désormais échappés à
            // l'affichage, du HTML ici s'afficherait en clair.
            ->with("message", "Suppression du dossier Nº" . $id . " réussie.");
    }

    /**
     * Corbeille: les dossiers supprimés, restaurables.
     *
     * La suppression étant devenue logique, il faut un endroit pour voir et
     * reprendre ce qui a été supprimé — sans quoi un dossier effacé par
     * erreur reste inaccessible tout en bloquant son connaissement.
     */
    public function trash()
    {
        $model = new ModelsTransitFolders();

        return view("transit_folders/trash", [
            "folders" => $model->onlyDeleted()->orderBy("deleted_at", "desc")->findAll(),
        ]);
    }

    public function restore()
    {
        $model = new ModelsTransitFolders();
        $id = $this->request->getPost("id");

        if (empty($id) or $model->onlyDeleted()->where("id", $id)->first() === null) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable dans la corbeille.");
        }

        try {
            $model->restore($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/modifier/" . $id)
            ->with("message", "Dossier Nº" . $id . " restauré.");
    }

    /**
     * Suivi d'exploitation: les dossiers en cours, groupés par étape.
     *
     * Répond à la question du matin — où ça bloque, et depuis combien de
     * temps — là où une liste triée par date d'ouverture ne dit rien de
     * l'étape à laquelle le dossier est coincé.
     */
    public function tracking()
    {
        $workflow = config(Workflow::class);
        $filtre = $this->request->getGet("etape");
        // Parenthèses obligatoires: « and » s'évalue après l'affectation.
        $filtre = (is_string($filtre) && isset($workflow->stages[$filtre])) ? $filtre : null;

        $parEtape = array_fill_keys(array_keys($workflow->stages), []);
        $lignes = [];

        foreach ((new ModelsTransitFolders())->tracked() as $folder) {
            $etape = $folder["stage"];

            if (!isset($parEtape[$etape])) {
                $parEtape[$etape] = [];
            }

            $parEtape[$etape][] = $folder;

            $jours = $folder["stage_days"] === null ? null : (int) $folder["stage_days"];
            $folder["en_retard"] = $workflow->isBlocked($etape, $jours);

            // Sans filtre, on ne montre que ce qui dépasse: un tableau de
            // deux cent cinquante lignes ne se lit pas, et la question posée
            // est « où ça bloque », pas « que contient le portefeuille ».
            if ($filtre === null ? $folder["en_retard"] : $etape === $filtre) {
                $lignes[] = $folder;
            }
        }

        return view("transit_folders/tracking", [
            "workflow" => $workflow,
            "parEtape" => $parEtape,
            "lignes" => $lignes,
            "filtre" => $filtre,
        ]);
    }

    /**
     * Dossiers dépassant le seuil de leur étape.
     *
     * L'ancienne règle retenait tout dossier ouvert depuis plus de trois
     * jours, sans distinguer l'étape: un dossier en douane depuis quatre
     * jours était signalé au même titre qu'un dossier livré mais non enlevé,
     * alors que le second fait courir des surestaries et pas le premier.
     */
    public function getCriticalFolders()
    {
        $workflow = config(Workflow::class);

        return array_values(array_filter(
            (new ModelsTransitFolders())->tracked(),
            static fn (array $folder): bool => $workflow->isBlocked(
                $folder["stage"],
                $folder["stage_days"] === null ? null : (int) $folder["stage_days"]
            )
        ));
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

        // Le quota est vérifié avant le déplacement: un fichier refusé ne
        // doit pas laisser de trace sur le disque.
        if (!quotas()->canStore($file->getSize())) {
            $limite = quotas()->storageLimitBytes();

            return redirect()
                ->back()
                ->with("error", "Espace de stockage épuisé (" . round($limite / 1048576) . " Mo pour votre offre).");
        }

        // Nom généré: le nom d'origine vient du client et ne doit jamais
        // atterrir tel quel sur le disque (collisions, traversée de chemin).
        // Le chemin est préfixé par l'agence car le numéro de dossier n'est
        // unique que par agence: sans ce préfixe, deux agences se
        // marcheraient dessus sur le disque.
        $storedName = $file->getRandomName();
        $relative = tenant_id() . "/" . $folderId . "/" . $storedName;
        // Lu avant move(), qui invalide l'objet source.
        $size = $file->getSize();

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
                "size" => $size,
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
