<?php

namespace App\Controllers;

use App\Models\DeclarationLines;
use App\Models\TransitFolders as ModelsTransitFolders;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Note de détail d'un dossier.
 *
 * C'est le document à partir duquel la déclaration est saisie dans GAINDE:
 * le déclarant y lit ligne par ligne. La page est donc conçue pour être
 * lue et imprimée, pas seulement remplie.
 */
class Declarations extends BaseController
{
    public function index($folderId)
    {
        $folder = $this->folder($folderId);
        $model = new DeclarationLines();
        $lignes = $model->forFolder($folderId);

        return view("declarations/index", [
            "folder" => $folder,
            "lignes" => $lignes,
            "totaux" => $model->totals($lignes),
            "prochaine" => $model->nextLineNo($folderId),
        ]);
    }

    public function print($folderId)
    {
        $folder = $this->folder($folderId);
        $model = new DeclarationLines();
        $lignes = $model->forFolder($folderId);

        return view("declarations/print", [
            "folder" => $folder,
            "lignes" => $lignes,
            "totaux" => $model->totals($lignes),
        ]);
    }

    /** En-tête: provenance, régime et agrément appartiennent au dossier. */
    public function saveHeader()
    {
        $data = $this->request->getPost();
        $folder = $this->folder($data["folder_id"] ?? null);
        $model = new ModelsTransitFolders();

        try {
            $model->update($folder["id"], [
                "provenance" => $data["provenance"] ?? null,
                "customs_regime" => $data["customs_regime"] ?? null,
                "agreement_number" => $data["agreement_number"] ?? null,
                "manifest" => $data["manifest"] ?? null,
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/declaration/" . $folder["id"])
            ->with("message", "En-tête enregistré.");
    }

    public function addLine()
    {
        $data = $this->request->getPost();
        $folder = $this->folder($data["folder_id"] ?? null);
        $model = new DeclarationLines();

        $code = preg_replace("/[^0-9]/", "", (string) ($data["hs_code"] ?? ""));

        if ($code === "") {
            return redirect()->back()->withInput()
                ->with("error", "L'espèce tarifaire est obligatoire.");
        }

        try {
            $model->insert([
                "folder_id" => $folder["id"],
                "line_no" => (int) ($data["line_no"] ?? $model->nextLineNo($folder["id"])),
                "hs_code" => $code,
                "description" => $data["description"] ?? null,
                "origin" => strtoupper(trim((string) ($data["origin"] ?? ""))) ?: null,
                "weight" => $this->montant($data["weight"] ?? null),
                "fob_value" => $this->montant($data["fob_value"] ?? null),
                "freight_value" => $this->montant($data["freight_value"] ?? null),
                "insurance_value" => $this->montant($data["insurance_value"] ?? null),
                "caf_value" => $this->montant($data["caf_value"] ?? null),
                "complementary_quantity" => $data["complementary_quantity"] ?? null,
                "container_chassis" => $data["container_chassis"] ?? null,
                "reference" => $data["reference"] ?? null,
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with("error", $th->getMessage());
        }

        if ($model->errors() !== []) {
            return redirect()->back()->withInput()->with("error", implode(" ", $model->errors()));
        }

        return redirect()
            ->to("dossiers/declaration/" . $folder["id"])
            ->with("message", "Ligne ajoutée.");
    }

    public function deleteLine()
    {
        $model = new DeclarationLines();
        $id = $this->request->getPost("id");
        $ligne = $model->find($id);

        if (!$ligne) {
            throw new PageNotFoundException("Ligne introuvable.");
        }

        try {
            $model->delete($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()
            ->to("dossiers/declaration/" . $ligne["folder_id"])
            ->with("message", "Ligne supprimée.");
    }

    /** Montant saisi avec des espaces ou une virgule décimale. */
    private function montant($valeur): ?float
    {
        if ($valeur === null or trim((string) $valeur) === "") {
            return null;
        }

        return (float) str_replace([" ", ","], ["", "."], (string) $valeur);
    }

    private function folder($folderId): array
    {
        $folder = empty($folderId) ? null : (new ModelsTransitFolders())->find($folderId);

        if (!$folder) {
            throw new PageNotFoundException("Dossier Nº" . $folderId . " introuvable.");
        }

        return $folder;
    }
}
