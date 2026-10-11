<?php

namespace App\Controllers;

use App\Libraries\ListOptions;
use App\Models\Payments;
use App\Models\TransitFolders;
use App\Models\Users;
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

    /** Colonnes sur lesquelles la vue d'ensemble accepte d'être triée. */
    private const TRIS = [
        "date" => "paid_at",
        "montant" => "amount",
        "mode" => "method",
        "dossier" => "folder_id",
    ];

    /**
     * Vue d'ensemble des encaissements.
     *
     * Sans elle, « qu'est-ce qui est rentré cette semaine, et par quel
     * mode ? » obligeait à ouvrir les factures une par une: les règlements
     * n'existaient que rattachés à la leur.
     */
    public function receipts()
    {
        $options = new ListOptions($this->request, self::TRIS, "date", "DESC", ["du", "au", "mode"]);

        // Le mois courant par défaut: c'est la période qu'on consulte en
        // tenant une caisse, et elle évite d'afficher tout l'historique à
        // l'ouverture.
        $du = $this->dateOrDefault($options->extra["du"] ?? null, date("Y-m-01"));
        $au = $this->dateOrDefault($options->extra["au"] ?? null, date("Y-m-d"));

        // Le mode vient de l'URL et finit dans un WHERE: il doit être l'un
        // de ceux que l'application propose, ou rien.
        $mode = $options->extra["mode"] ?? null;
        $mode = isset(Payments::METHODES[$mode]) ? $mode : null;

        $model = new Payments();
        $model->where("paid_at >=", $du)->where("paid_at <=", $au);

        if ($mode !== null) {
            $model->where("method", $mode);
        }

        if ($options->search !== "") {
            // Parenthésé: sans cela le OR s'affranchirait du cloisonnement.
            $model->groupStart()
                ->like("reference", $options->search)
                ->orLike("note", $options->search)
                ->orLike("folder_id", $options->search)
                ->groupEnd();
        }

        $reglements = $model
            ->orderBy($options->column(), $options->dir)
            ->orderBy("id", "DESC")
            ->paginate($options->perPage);

        return view("invoices/receipts", [
            "reglements" => $reglements,
            "pager" => $model->pager,
            "options" => $options,
            "du" => $du,
            "au" => $au,
            "mode" => $mode,
            "methodes" => Payments::METHODES,
            // Les totaux portent sur la période entière, pas sur la page.
            "totaux" => (new Payments())->periodTotals($du, $au, $mode),
            "rattachements" => $this->attach($reglements),
        ]);
    }

    /**
     * Noms de clients et auteurs de saisie des règlements affichés.
     *
     * Trois requêtes groupées pour toute la page, au lieu d'une par ligne.
     *
     * @param list<array<string, mixed>> $reglements
     * @return array{clients: array<string, string>, auteurs: array<int, string>}
     */
    private function attach(array $reglements): array
    {
        $folderIds = array_values(array_unique(array_filter(array_column($reglements, "folder_id"))));
        $auteurIds = array_values(array_unique(array_filter(array_column($reglements, "recorded_by"))));

        $clientParDossier = [];

        if ($folderIds !== []) {
            // Le modèle des dossiers rattache lui-même le client: invoice_to
            // porte la fiche entière, pas l'identifiant. Rechercher les noms
            // ici referait un travail déjà fait, en une requête de plus.
            foreach ((new TransitFolders())->whereIn("id", $folderIds)->findAll() as $dossier) {
                $clientParDossier[(string) $dossier["id"]] = $dossier["invoice_to"]["name"] ?? null;
            }
        }

        return [
            "clients" => $clientParDossier,
            "auteurs" => $auteurIds === [] ? [] : array_column(
                (new Users())->whereIn("id", $auteurIds)->findAll(),
                "name",
                "id"
            ),
        ];
    }

    /** Une date invalide ou absente retombe sur la valeur par défaut. */
    private function dateOrDefault(?string $valeur, string $defaut): string
    {
        if ($valeur === null or !preg_match("/^\d{4}-\d{2}-\d{2}$/", $valeur)) {
            return $defaut;
        }

        return checkdate((int) substr($valeur, 5, 2), (int) substr($valeur, 8, 2), (int) substr($valeur, 0, 4))
            ? $valeur
            : $defaut;
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
