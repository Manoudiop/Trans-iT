<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Controllers\TransitFolders as ControllersTransitFolders;
use App\Models\Clients;
use App\Models\TransitFolders;
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

        $user = $modele
            ->where("email", (string) ($data["email"] ?? ""))
            ->first();

        if (!$user or !$modele->verifyAndRehash($user, (string) ($data["password"] ?? ""))) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", true);
        }

        // Le hash n'a rien à faire en session, et l'identifiant de session est
        // renouvelé pour couper toute fixation antérieure à la connexion.
        unset($user["password"]);
        session()->regenerate();
        session()->set("userData", $user);
        return redirect()
            ->to("/tableau-de-bord");
    }

    public function logout()
    {
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

        if (isset($data["id"])) {
            //unique email validation
            if (isset($data["email"])) {
                $duplicate = $modele
                    ->select("id")
                    ->where("email", $data["email"])
                    ->first();
                // first() renvoie null quand l'email est libre: pas de count() ici.
                if ($duplicate and $duplicate["id"] != $data["id"]) {
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with("error", "Email en doublon.");
                }
            }
            $message = "Modifications enregistrées.";
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
