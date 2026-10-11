<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Clients;
use App\Models\Tenants;
use App\Models\TransitFolders;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;

class Invoices extends BaseController
{
    public function index()
    {
        $modele = new TransitFolders();
        $r = $this->request->getGet("r");
        if ($r) {
            $modele
                ->like("reference", $r)
                ->orLike("id", $r);
        }
        return view("invoices/index", [
            "invoices" => $modele
                ->where("invoiced", true)
                ->find()
        ]);
    }

    /**
     * Suivi des avances: ce que chaque client doit, et depuis quand.
     *
     * Une maison de transit décaisse les droits, le magasinage et les
     * surestaries avant d'être payée. Savoir combien de trésorerie est
     * immobilisée, chez qui et depuis quand, pèse plus lourd au quotidien
     * que le chiffre d'affaires.
     */
    public function outstanding()
    {
        $lignes = (new TransitFolders())->outstandingByClient();

        // Les noms de clients en une requête, pas une par ligne.
        $noms = [];
        $ids = array_values(array_filter(array_column($lignes, "client_id")));
        if ($ids !== []) {
            $noms = array_column(
                (new Clients())->whereIn("id", $ids)->findAll(),
                "name",
                "id"
            );
        }

        foreach ($lignes as &$ligne) {
            $ligne["client_nom"] = $noms[$ligne["client_id"]] ?? "INFORMATIONS INDISPONIBLES";
        }
        unset($ligne);

        return view("invoices/outstanding", [
            "lignes" => $lignes,
            "invoicing" => config(\Config\Invoicing::class),
        ]);
    }

    public function notInvoiced()
    {
        $modele = new TransitFolders();
        return view("invoices/notInvoiced", [
            "invoices" => $modele
                ->where("invoiced", false)
                ->find()
        ]);
    }

    public function invoicePage($id)
    {
        $modele = new TransitFolders();
        $invoice = $modele->find($id);

        if (!$invoice) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("invoices/invoice", $invoice);
    }

    public function invoice($id)
    {
        $modele = new TransitFolders();
        $data = $this->request->getPost();
        $data["invoiced"] = true;

        //cooking id
        if (empty($data["reference"])) {
            $data["reference"] = strtotime(Time::now());
        }

        try {
            $modele->update($id, $data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/information/" . $id . "#facture")
            ->with("message", "Facturation du dossier " . $id . " réussie.");
    }

    public function editPage($id)
    {
        $modele = new TransitFolders();
        $invoice = $modele->find($id);

        if (!$invoice) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("invoices/edit", $invoice);
    }

    public function edit($id)
    {
        $modele = new TransitFolders();
        $data = $this->request->getPost();

        try {
            $modele->update($id, $data);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->withInput()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/information/" . $id . "#facture")
            ->with("message", "Modification de la facture du dossier " . $id . " réussie.");
    }

    public function deletePage($id)
    {
        $modele = new TransitFolders();
        $invoice = $modele->find($id);

        if (!$invoice) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        return view("invoices/delete", $invoice);
    }

    public function delete($id)
    {
        $data = $this->request->getPost();
        $modele = new TransitFolders();

        if ($data["id"] != $id) {
            return redirect()
                ->back()
                ->with("error", "Échec de la confirmation de suppression.");
        }

        try {
            $modele->update($id, [
                "invoiced" => false,
            ]);
        } catch (\Throwable $th) {
            return redirect()
                ->back()
                ->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/information/" . $id)
            ->with("message", "Suppression de la facture réussie.");
    }

    public function print($id)
    {
        $modele = new TransitFolders();
        $invoice = $modele->find($id);

        if (!$invoice) {
            throw new PageNotFoundException("Dossier Nº" . $id . " introuvable.");
        }

        // Le découpage en sections vient de la configuration, et les montants
        // restent accessibles par nom de poste: la vue n'a plus à énumérer
        // les vingt-et-un débours un par un, ni à réécrire leur somme.
        // Copié avant d'ajouter quoi que ce soit: montants ne porte que le
        // dossier.
        $invoice["montants"] = $invoice;
        $invoice["invoicing"] = config(\Config\Invoicing::class);

        // L'en-tête portait un nom d'entreprise et un texte de remplissage
        // écrits en dur. Chaque agence doit voir le sien.
        $invoice["agence"] = (new Tenants())->find(tenant_id());

        return view("invoices/print", $invoice);
    }

    public function getSalesFigures($from, $to)
    {
        // Une somme calculée en base, au lieu de charger toutes les factures
        // de la période — chacune déclenchant le rattachement de ses colis,
        // de son client, de son auteur et de ses pièces jointes.
        $row = (new TransitFolders())
            ->selectSum("invoice_amount", "total")
            ->where("closed", true)
            ->where("invoice_date >=", $from)
            ->where("invoice_date <=", $to)
            ->first();

        return (float) ($row["total"] ?? 0);
    }
}
