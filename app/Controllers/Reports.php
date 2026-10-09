<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\TransitFolders;

class Reports extends BaseController
{
    public function index()
    {
        return view("reports/index");
    }

    public function transitFolders()
    {
        $folders = [];
        $modele = new TransitFolders();

        $req = $this->request->getGet();

        // Un filtre partiel — lien tronqué, paramètre oublié — levait une
        // erreur sur une clé absente: le code supposait que la présence d'un
        // paramètre garantissait celle des quatre. Chaque critère est
        // désormais lu indépendamment, et la période est obligatoire.
        if (!empty($req["from"]) and !empty($req["to"])) {
            if (!empty($req["invoice_to"]) and $req["invoice_to"] !== "all") {
                $modele->where("invoice_to", $req["invoice_to"]);
            }

            if (!empty($req["type"]) and $req["type"] !== "all") {
                $modele->where("type", $req["type"]);
            }

            $folders = $modele
                ->where("open_date >=", $req["from"])
                ->where("open_date <=", $req["to"])
                ->findAll();
        }


        return view("reports/folders", [
            "clients" => (new Clients())->orderBy("name")->findAll(),
            "folders" => $folders
        ]);
    }

    public function transitInvoices()
    {
        $invoices = [];
        $modele = new TransitFolders();
        $modele->where("invoiced", true);

        $req = $this->request->getGet();

        // Même correction que pour le rapport des dossiers.
        if (!empty($req["from"]) and !empty($req["to"])) {
            if (!empty($req["invoice_to"]) and $req["invoice_to"] !== "all") {
                $modele->where("invoice_to", $req["invoice_to"]);
            }

            if (!empty($req["type"]) and $req["type"] !== "all") {
                $modele->where("type", $req["type"]);
            }

            $invoices = $modele
                ->where("invoice_date >=", $req["from"])
                ->where("invoice_date <=", $req["to"])
                ->findAll();
        }


        return view("reports/invoices", [
            "clients" => (new Clients())->orderBy("name")->findAll(),
            "invoices" => $invoices
        ]);
    }
}
