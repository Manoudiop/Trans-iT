<?php

namespace App\Controllers;

use App\Models\CashAccounts;
use App\Models\CashMovements;
use App\Models\TransitFolders;
use App\Models\Users as ModelsUsers;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Cash;

/**
 * Caisse et banque.
 *
 * Nommé Treasury pour ne pas entrer en collision avec Config\Cash.
 */
class Treasury extends BaseController
{
    public function index()
    {
        $config = config(Cash::class);
        $mouvements = new CashMovements();

        $journal = $mouvements->journal();

        return view("treasury/index", [
            "config" => $config,
            "comptes" => (new CashAccounts())->withBalances(),
            "detentions" => $this->namedHoldings($mouvements->agentHoldings()),
            "journal" => $journal,
            "agents" => (new ModelsUsers())->orderBy("name", "asc")->findAll(),
            "noms" => $this->namesFor($journal),
        ]);
    }

    /**
     * Rapprochement décaissé / facturé.
     *
     * Trois situations méritent une action, et elles sont séparées parce
     * qu'elles n'appellent pas la même: facturer, corriger la facture, ou
     * compléter la saisie de caisse.
     */
    public function reconciliation()
    {
        $lignes = (new TransitFolders())->reconciliation();

        $groupes = ["a_facturer" => [], "sous_facture" => [], "a_verifier" => [], "conformes" => []];
        $totaux = ["a_facturer" => 0.0, "sous_facture" => 0.0, "a_verifier" => 0.0];
        $sansSaisie = ["dossiers" => 0, "montant" => 0.0];

        foreach ($lignes as $ligne) {
            $ecart = (float) $ligne["ecart"];
            $decaisse = (float) $ligne["decaisse"];

            if (!$ligne["invoiced"] and $decaisse > 0) {
                // De l'argent est sorti sans qu'aucune facture n'existe.
                $groupes["a_facturer"][] = $ligne;
                $totaux["a_facturer"] += $decaisse;
                continue;
            }

            // Une facture sans aucun décaissement saisi n'est pas une
            // anomalie: c'est de la donnée qui manque. Les compter comme des
            // écarts noierait le signal sous l'historique — à la mise en
            // service, la totalité du portefeuille apparaîtrait en alerte.
            if ($decaisse <= 0) {
                $sansSaisie["dossiers"]++;
                $sansSaisie["montant"] += (float) $ligne["facture_debours"];
                continue;
            }

            // Tolérance d'un franc: les arrondis ne sont pas des anomalies.
            if ($ecart < -1) {
                $groupes["sous_facture"][] = $ligne;
                $totaux["sous_facture"] += -$ecart;
            } elseif ($ecart > 1) {
                $groupes["a_verifier"][] = $ligne;
                $totaux["a_verifier"] += $ecart;
            } else {
                $groupes["conformes"][] = $ligne;
            }
        }

        return view("treasury/reconciliation", [
            "groupes" => $groupes,
            "totaux" => $totaux,
            "sansSaisie" => $sansSaisie,
        ]);
    }

    public function add()
    {
        $data = $this->request->getPost();
        $config = config(Cash::class);

        $kind = (string) ($data["kind"] ?? "");
        $agentId = $data["agent_id"] ?? null;
        $categorie = (string) ($data["category"] ?? "");
        $montant = (float) str_replace([" ", ","], ["", "."], (string) ($data["amount"] ?? "0"));

        $erreur = $this->validateMovement($config, $kind, $montant, $agentId, $categorie, $data);
        if ($erreur !== null) {
            return redirect()->back()->withInput()->with("error", $erreur);
        }

        // Une dépense justifiée par un agent ne sort pas du compte: l'argent
        // en est parti au moment de l'avance.
        //
        // Toutes les clés sont lues avec ?? : un formulaire ne poste pas les
        // champs qu'il n'affiche pas, et lire une clé absente lève une
        // ErrorException en développement.
        $compte = ($kind === "depense" and !empty($agentId))
            ? null
            : (($data["account_id"] ?? null) ?: null);

        $model = new CashMovements();

        try {
            $model->insert([
                "account_id" => $compte,
                "kind" => $kind,
                "amount" => $montant,
                "moved_at" => ($data["moved_at"] ?? "") ?: date("Y-m-d"),
                "category" => $kind === "depense" ? $categorie : null,
                "folder_id" => ($data["folder_id"] ?? null) ?: null,
                "agent_id" => $agentId ?: null,
                "reference" => $data["reference"] ?? null,
                "note" => $data["note"] ?? null,
                "recorded_by" => session()->userData["id"] ?? null,
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with("error", $th->getMessage());
        }

        if ($model->errors() !== []) {
            return redirect()->back()->withInput()->with("error", implode(" ", $model->errors()));
        }

        return redirect()->to("tresorerie")->with("message", "Mouvement enregistré.");
    }

    public function delete()
    {
        $model = new CashMovements();
        $id = $this->request->getPost("id");

        if (!$model->find($id)) {
            throw new PageNotFoundException("Mouvement introuvable.");
        }

        try {
            $model->delete($id);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()->to("tresorerie")->with("message", "Mouvement supprimé.");
    }

    public function addAccount()
    {
        $model = new CashAccounts();
        $data = $this->request->getPost();

        try {
            $model->insert([
                "name" => $data["name"] ?? "",
                "type" => $data["type"] ?? "caisse",
                "opening_balance" => (float) ($data["opening_balance"] ?? 0),
                "active" => 1,
            ]);
        } catch (\Throwable $th) {
            return redirect()->back()->withInput()->with("error", $th->getMessage());
        }

        if ($model->errors() !== []) {
            return redirect()->back()->withInput()->with("error", implode(" ", $model->errors()));
        }

        return redirect()->to("tresorerie")->with("message", "Compte créé.");
    }

    /** Renvoie le message d'erreur, ou null si la saisie tient debout. */
    private function validateMovement(Cash $config, string $kind, float $montant, $agentId, string $categorie, array $data): ?string
    {
        if (!isset($config->kinds[$kind])) {
            return "Nature de mouvement inconnue.";
        }

        if ($montant <= 0) {
            return "Le montant doit être supérieur à zéro.";
        }

        $regleAgent = $config->kinds[$kind]["agent"];

        if ($regleAgent === "requis" and empty($agentId)) {
            return "Indiquez l'agent concerné.";
        }

        if ($regleAgent === "interdit" and !empty($agentId)) {
            return "Une recette ne se rattache pas à un agent.";
        }

        // Le compte n'est exigé que lorsque l'argent y entre ou en sort.
        $sansCompte = ($kind === "depense" and !empty($agentId));
        if (!$sansCompte and empty($data["account_id"])) {
            return "Choisissez le compte concerné.";
        }

        if ($kind === "depense") {
            if ($categorie === "" or !isset($config->categories[$categorie])) {
                return "Choisissez une catégorie de dépense.";
            }

            // Sans dossier, un frais de dossier ne peut pas être comparé à ce
            // qui a été facturé — c'est tout l'intérêt de la saisie.
            if ($config->isFolderCategory($categorie) and empty($data["folder_id"])) {
                return "Cette catégorie concerne un dossier: indiquez son numéro.";
            }

            if (!empty($data["folder_id"]) and !(new TransitFolders())->find($data["folder_id"])) {
                return "Dossier Nº" . $data["folder_id"] . " introuvable.";
            }
        }

        return null;
    }

    /** Ajoute le nom de l'agent à chaque ligne de détention. */
    private function namedHoldings(array $detentions): array
    {
        if ($detentions === []) {
            return [];
        }

        $noms = array_column(
            (new ModelsUsers())->whereIn("id", array_column($detentions, "agent_id"))->findAll(),
            "name",
            "id"
        );

        foreach ($detentions as &$ligne) {
            $ligne["agent_nom"] = $noms[$ligne["agent_id"]] ?? "Agent inconnu";
        }
        unset($ligne);

        return $detentions;
    }

    /**
     * Noms des comptes et des agents cités par le journal, en deux requêtes.
     *
     * @return array{comptes: array<int, string>, agents: array<int, string>}
     */
    private function namesFor(array $journal): array
    {
        $comptes = array_values(array_filter(array_column($journal, "account_id")));
        $agents = array_values(array_filter(array_column($journal, "agent_id")));

        return [
            "comptes" => $comptes === [] ? [] : array_column(
                (new CashAccounts())->whereIn("id", $comptes)->findAll(),
                "name",
                "id"
            ),
            "agents" => $agents === [] ? [] : array_column(
                (new ModelsUsers())->whereIn("id", $agents)->findAll(),
                "name",
                "id"
            ),
        ];
    }
}
