<?php

namespace App\Controllers;

use App\Controllers\BaseController;
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
