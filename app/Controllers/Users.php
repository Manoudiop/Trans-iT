<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\TransitFolders as ControllersTransitFolders;
use App\Models\Clients;
use App\Models\TransitFolders;
use App\Models\Tenants;
use App\Models\Users as ModelsUsers;
use CodeIgniter\Exceptions\PageNotFoundException;

class Users extends BaseController
{
    public function index(): string
    {
        return view("login");
    }

    public function login()
    {
        $data = $this->request->getPost();
        $modele = new ModelsUsers();

        $candidats = $modele->findAllForLogin((string) ($data["email"] ?? ""));

        // Le sous-domaine, quand il désigne une agence, restreint la
        // recherche: c'est lui qui lève l'ambiguïté d'un email partagé par
        // plusieurs agences depuis que l'unicité n'est plus globale.
        $viaHote = tenant()->tenantFromHost();
        if ($viaHote !== null) {
            $candidats = array_values(array_filter(
                $candidats,
                static fn (array $u): bool => (int) $u["tenant_id"] === (int) $viaHote["id"]
            ));
        }

        if ($candidats === []) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", true);
        }

        if (count($candidats) > 1) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Cet email est utilisé par plusieurs agences: connectez-vous depuis le sous-domaine de la vôtre.");
        }

        $user = $candidats[0];

        // L'agence est déduite du compte et fixée avant toute écriture: la
        // conversion d'un ancien hash SHA-1 passe par un modèle cloisonné,
        // qui exige un contexte.
        tenant()->set((int) $user["tenant_id"]);

        if (!$modele->verifyAndRehash($user, (string) ($data["password"] ?? ""))) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", true);
        }

        $agence = (new Tenants())->find($user["tenant_id"]);
        if (!$agence or !$agence["active"]) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Accès suspendu: contactez l'administrateur.");
        }

        // Le hash n'a rien à faire en session, et l'identifiant de session est
        // renouvelé pour couper toute fixation antérieure à la connexion.
        unset($user["password"]);
        session()->regenerate();
        session()->set("userData", $user);
        session()->set("tenantId", (int) $user["tenant_id"]);
        return redirect()
            ->to("/tableau-de-bord");
    }

    public function logout()
    {
        // Les clés sont retirées avant destroy(), qui ne vide que le stockage
        // et laisse $_SESSION en mémoire: toute écriture ultérieure
        // réenregistrerait la session authentifiée.
        session()->remove(["userData", "tenantId"]);
        session()->destroy();
        return redirect()->to("/");
    }

    public function dashboard()
    {
        if (session()->userData["profile"] != "ADMIN") {
            return redirect()->to("dossiers");
        }

        $sales_chart = [];
        for ($i = 1; $i <= 12; $i++) {
            array_push($sales_chart, (new Invoices)->getSalesFigures(date("Y-" . sprintf("%02d", $i) . "-01"), date("Y-" . sprintf("%02d", $i) . "-31")));
        }

        $clients_chart = [];
        for ($i = 1; $i <= 12; $i++) {
            array_push($clients_chart, $i <= date("m") ? (new Clients())
                ->where("created_at >=", date("Y-" . sprintf("%02d", $i) . "-01"))
                ->where("created_at <=", date("Y-" . sprintf("%02d", $i) . "-31"))
                ->countAllResults() : null);
        }
        return view("dashboard", [
            "sales_figures" => (new Invoices())->getSalesFigures(date("Y-m-01"), date("Y-m-d")),
            "clients_count" => (new Clients())->countAllResults(),
            "in_progress_folders_count" => (new TransitFolders())->where("closed", false)->countAllResults(),
            "month_folder" => (new TransitFolders())->where("MONTH(open_date)", date("m"))->where("YEAR(open_date)", date("Y"))->countAllResults(),
            "pie_type_imp" => (new TransitFolders())
                ->where("closed", true)
                ->where("type", "IMP")
                ->countAllResults(),
            "pie_type_exp" => (new TransitFolders())
                ->where("closed", true)
                ->where("type", "EXP")
                ->countAllResults(),
            "sales_chart" => $sales_chart,
            "clients_chart" => $clients_chart,
            "criticals" => (new ControllersTransitFolders())->getCriticalFolders(),
            "notInvoiced" => (new TransitFolders())
                ->where("invoiced", false)
                ->find()
        ]);
    }

    public function list()
    {
        $modele = new ModelsUsers();
        $search = $this->request->getGet("r");
        if ($search) {
            $modele
                ->like("name", $search)
                ->orLike("email", $search);
        }
        return view("users/index.php", [
            "users" => $modele->orderBy("name", "asc")->find()
        ]);
    }

    public function save()
    {
        $modele = new ModelsUsers();
        $data = $this->request->getPost();
        $generated = null;

        // L'email n'est plus unique que par agence: le doublon se cherche
        // donc via le modèle cloisonné, dans l'agence courante.
        if (isset($data["email"]) and $data["email"] !== "") {
            $duplicate = $modele
                ->select("id")
                ->where("email", $data["email"])
                ->first();
            if ($duplicate and (!isset($data["id"]) or $duplicate["id"] != $data["id"])) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with("error", "Cet email est déjà utilisé.");
            }
        }

        if (isset($data["id"])) {
            $message = "Modifications enregistrées.";
        } elseif (!quotas()->canAddUser()) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", "Limite de " . quotas()->usersLimit() . " utilisateurs atteinte pour votre offre.");
        } else {
            // Mot de passe initial tiré au hasard: aucun secret partagé ne
            // traîne dans le dépôt ni dans le formulaire de création.
            $generated = bin2hex(random_bytes(8));
            $data["password"] = $generated;
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

        $redirection = redirect()
            ->back()
            ->with("message", $message);

        return $generated
            ? $redirection->with("new_password", $generated)
            : $redirection;
    }

    public function delete()
    {
        $modele = new ModelsUsers();
        $data = $this->request->getPost();
        try {
            $modele->delete($data["id"]);
            if (session()->userData["id"] == $data["id"]) {
                session()->destroy();
            }
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with('error', $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", "Suppression réussie.");
    }

    public function editPage($id)
    {
        $modele = new ModelsUsers();
        $user = $modele->find($id);
        if (!$user) {
            throw new PageNotFoundException("Utilisateur introuvable.");
        }
        return view("users/edit", $user);
    }

    public function addPage()
    {
        return view("users/add");
    }

    public function editPwd()
    {
        $data = $this->request->getPost();

        if ($data["passwordn"] != $data["passwordc"]) {
            return redirect()
                ->back()
                ->with("error", "Échec de la confirmation de mot de passe.");
        }
        $modele = new ModelsUsers();
        $user = $modele->find(session()->userData["id"]);
        if (!$user) {
            session()->destroy();
            return redirect()->to("/");
        }

        if (!$modele->verifyAndRehash($user, (string) ($data["password"] ?? ""))) {
            return redirect()
                ->back()
                ->with("error", "Mot de passe actuel incorrect.");
        }

        try {
            $modele->update($user["id"], ["password" => $data["passwordn"]]);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", "Modification réussie.");
    }
}
