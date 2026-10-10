<?php

namespace App\Controllers;

use App\Models\Payments;
use App\Models\TransitFolders;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Règlements d'une facture.
 *
 * Nommé Settlements et non Payments pour ne pas entrer en collision avec le
 * modèle du même nom dans les vues et les imports.
 */
class Settlements extends BaseController
{
    public function index($folderId)
    {
        $folder = $this->invoicedFolder($folderId);

        return view("invoices/settlements", [
            "folder" => $folder,
            "payments" => (new Payments())->forFolder($folderId),
            "methodes" => Payments::METHODES,
            "solde" => $this->balance($folder),
        ]);
    }

    public function add()
    {
        $data = $this->request->getPost();
        $folderId = $data["folder_id"] ?? null;
        $folder = $this->invoicedFolder($folderId);

        $montant = (float) str_replace([" ", ","], ["", "."], (string) ($data["amount"] ?? "0"));
        $solde = $this->balance($folder);

        if ($montant <= 0) {
            return redirect()->back()->withInput()
                ->with("error", "Le montant du règlement doit être supérieur à zéro.");
        }

        // Un encaissement supérieur au solde est presque toujours une faute
        // de frappe. Le refuser évite un encours négatif qui fausserait la
        // balance de tout le client.
        if ($montant > $solde + 0.01) {
            return redirect()->back()->withInput()
                ->with("error", "Le montant dépasse le solde restant dû ("
                    . number_format($solde, 0, ",", " ") . " FCFA).");
        }

        $model = new Payments();

        try {
            $model->insert([
                "folder_id" => $folder["id"],
                "amount" => $montant,
                // ?? et non accès direct: un champ absent du POST lèverait
                // une ErrorException.
                "paid_at" => ($data["paid_at"] ?? "") ?: date("Y-m-d"),
                "method" => $data["method"] ?? null,
                "reference" => $data["reference"] ?? null,
                "note" => $data["note"] ?? null,
                "recorded_by" => session()->userData["id"] ?? null,
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with("error", $th->getMessage());
        }

        if ($model->errors() !== []) {
            return redirect()->back()->withInput()
                ->with("error", implode(" ", $model->errors()));
        }

        $restant = $solde - $montant;

        return redirect()
            ->to("factures/reglements/" . $folder["id"])
            ->with("message", $restant <= 0.01
                ? "Règlement enregistré. La facture est soldée."
                : "Règlement enregistré. Reste dû: " . number_format($restant, 0, ",", " ") . " FCFA.");
    }

    public function delete()
    {
        $id = $this->request->getPost("id");
        $model = new Payments();
        $payment = $model->find($id);

        if (!$payment) {
            throw new PageNotFoundException("Règlement introuvable.");
        }

        try {
            $model->delete($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()
            ->to("factures/reglements/" . $payment["folder_id"])
            ->with("message", "Règlement supprimé.");
    }

    /** Dossier facturé de l'agence courante, ou 404. */
    private function invoicedFolder($folderId): array
    {
        $folder = empty($folderId) ? null : (new TransitFolders())->find($folderId);

        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $folderId . " introuvable.");
        }

        if (!$folder["invoiced"]) {
            throw new PageNotFoundException("Le dossier Nº" . $folderId . " n'est pas encore facturé.");
        }

        return $folder;
    }

    private function balance(array $folder): float
    {
        return (float) $folder["invoice_amount"] - (float) $folder["paid_amount"];
    }
}
