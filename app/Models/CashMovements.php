<?php

namespace App\Models;

use Config\Cash;

/**
 * Journal de trésorerie.
 *
 * Une seule table pour tous les flux. Le sens du mouvement vient de sa
 * nature, jamais du signe du montant: une saisie négative par mégarde ne
 * peut donc pas inverser une écriture.
 */
class CashMovements extends TenantModel
{
    protected $table            = 'cash_movements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "account_id",
        "kind",
        "amount",
        "moved_at",
        "category",
        "folder_id",
        "agent_id",
        "reference",
        "note",
        "recorded_by",
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        "kind" => "required|in_list[recette,depense,avance,retour]",
        "amount" => "required|greater_than[0]",
        "moved_at" => "required|valid_date",
    ];

    protected $validationMessages = [
        "amount" => ["greater_than" => "Le montant doit être supérieur à zéro."],
    ];

    /**
     * Expression SQL donnant l'effet d'un mouvement sur le solde du compte.
     *
     * Une dépense justifiée par un agent ne touche pas le compte: l'argent
     * en est déjà sorti au moment de l'avance.
     */
    private static function effectSql(): string
    {
        return "CASE"
            . " WHEN `kind` = 'recette' THEN `amount`"
            . " WHEN `kind` = 'retour' THEN `amount`"
            . " WHEN `kind` = 'avance' THEN -`amount`"
            . " WHEN `kind` = 'depense' AND `agent_id` IS NULL THEN -`amount`"
            . " ELSE 0 END";
    }

    /**
     * Variation de solde par compte.
     *
     * @return array<int, float>
     */
    public function balancesByAccount(): array
    {
        $rows = $this->select("`account_id`, SUM(" . self::effectSql() . ") AS total", false)
            ->groupBy("account_id")
            ->findAll();

        return array_map("floatval", array_column($rows, "total", "account_id"));
    }

    /**
     * Ce que chaque agent détient encore.
     *
     * Avances reçues, moins dépenses qu'il a justifiées, moins liquide
     * rendu. Aucun bon de caisse n'est nécessaire: le solde se lit dans le
     * journal.
     *
     * @return list<array<string, mixed>>
     */
    public function agentHoldings(): array
    {
        return $this->select(
            "`agent_id`,"
            . " SUM(CASE WHEN `kind` = 'avance' THEN `amount` ELSE 0 END) AS avances,"
            . " SUM(CASE WHEN `kind` = 'depense' THEN `amount` ELSE 0 END) AS justifie,"
            . " SUM(CASE WHEN `kind` = 'retour' THEN `amount` ELSE 0 END) AS rendu,"
            . " SUM(CASE WHEN `kind` = 'avance' THEN `amount`"
            . " WHEN `kind` IN ('depense','retour') THEN -`amount` ELSE 0 END) AS detenu",
            false
        )
            ->where("agent_id IS NOT NULL", null, false)
            ->groupBy("agent_id")
            ->having("detenu <> 0", null, false)
            ->findAll();
    }

    /**
     * Décaissements rattachés à un dossier.
     *
     * @return array<int|string, float>
     */
    public function spentByFolder(array $folderIds = []): array
    {
        $builder = $this->select("`folder_id`, SUM(`amount`) AS total", false)
            ->where("kind", "depense")
            ->where("folder_id IS NOT NULL", null, false);

        if ($folderIds !== []) {
            $builder->whereIn("folder_id", $folderIds);
        }

        $rows = $builder->groupBy("folder_id")->findAll();

        return array_map("floatval", array_column($rows, "total", "folder_id"));
    }

    /** Journal, du plus récent au plus ancien. */
    public function journal(int $limit = 200): array
    {
        return $this->orderBy("moved_at", "desc")
            ->orderBy("id", "desc")
            ->findAll($limit);
    }

    /** Total des charges de fonctionnement sur une période. */
    public function overheadBetween(string $from, string $to): float
    {
        $categories = array_keys(config(Cash::class)->overheadCategories());

        if ($categories === []) {
            return 0.0;
        }

        $row = $this->selectSum("amount", "total")
            ->where("kind", "depense")
            ->whereIn("category", $categories)
            ->where("moved_at >=", $from)
            ->where("moved_at <=", $to)
            ->first();

        return (float) ($row["total"] ?? 0);
    }
}
