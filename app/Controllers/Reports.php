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

        if (!empty($req)) {

            if ($req["invoice_to"] != "all") {
                $modele->where("invoice_to", $req["invoice_to"]);
            }

            if ($req["type"] != "all") {
                $modele->where("type", $req["type"]);
            }

            $modele
                ->where("open_date >=", $req["from"]);

            $modele
                ->where("open_date <=", $req["to"]);

            $folders = $modele->find();
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

        if (!empty($req)) {

            if ($req["invoice_to"] != "all") {
                $modele->where("invoice_to", $req["invoice_to"]);
            }

            if ($req["type"] != "all") {
                $modele->where("type", $req["type"]);
            }

            $modele->where("invoice_date >=", $req["from"]);
            $modele->where("invoice_date <=", $req["to"]);

            $invoices = $modele->find();
        }


        return view("reports/invoices", [
            "clients" => (new Clients())->orderBy("name")->findAll(),
            "invoices" => $invoices
        ]);
    }
}
