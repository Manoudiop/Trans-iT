<?php

namespace App\Models;

use Config\Invoicing;

class TransitFolders extends TenantModel
{
    protected $table            = 'transit_folders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'open_date',
        'handling_agent',
        'repository',
        'orbus_number',
        'expeditor',
        'bl',
        'bl_of',
        'boat',
        'boat_of',
        'manifest',
        'article',
        'declaration',
        'article',
        'recipient',
        'recipient_address',
        'invoice_to',
        'transit_order',
        'transit_order_date',
        'invoice',
        'invoice_date',
        'receipt',
        'receipt_date',
        'check',
        'check_date',
        'customs_admission_date',
        'customs_inspector',
        'bae_date',
        'delivery_date',
        'reserve',
        'missing',
        'type',
        "closed",
        "invoiced",
        "reference",
        "invoice_author",
        "designation",
        "duties_taxes",
        "agios",
        "bl_stamp",
        "shipping_taxe",
        "boarding_disembarkation",
        "storing_guarding",
        "container_transportation",
        "handling",
        "insurance",
        "transportation",
        "expert_report",
        "customs_excort",
        "demurrage",
        "customs_clearance",
        "postal_package_withdrawal_fees",
        "customs_ts_visit",
        "full_land_rental",
        "visit_admissibility",
        "indirect_fees",
        "orbus_fees",
        "trucking",
        "grouping",
        "commission_on_disbursements",
        "folder_opening_fees",
        "transit_commission",
        "customs_honorary_fees",
        "had",
        "internal_handling",
        "loading_unloading",
        "printer",
        "procedures_formalities",
        "tps",
        "freight",
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ["getFolderItems"];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Remet un dossier supprimé en service.
     *
     * deleted_at est volontairement absent de $allowedFields, pour qu'aucun
     * POST ne puisse supprimer ou restaurer un dossier en douce. La
     * restauration passe donc par le query builder, avec le filtre d'agence
     * écrit explicitement puisque beforeUpdate n'est pas déclenché ici.
     */
    public function restore($id): bool
    {
        return (bool) $this->builder()
            ->where($this->table . ".tenant_id", tenant_id())
            ->where($this->table . "." . $this->primaryKey, $id)
            ->update([$this->deletedField => null]);
    }

    /**
     * Dossier supprimé portant ce connaissement, s'il en existe un.
     *
     * Les index uniques ignorent deleted_at: un dossier à la corbeille
     * continue d'occuper son numéro de connaissement. Sans ce contrôle,
     * ressaisir le dossier renverrait une erreur de doublon incompréhensible.
     */
    public function deletedHolderOfBl(string $bl): ?array
    {
        return $this->onlyDeleted()
            ->where("bl", $bl)
            ->first();
    }

    /**
     * Rapprochement entre les débours facturés et les sommes réellement
     * décaissées, dossier par dossier.
     *
     * C'est le contrôle qui transforme la saisie de trésorerie en argent
     * récupéré: une surestarie payée mais oubliée à la facturation, ou un
     * débours refacturé au mauvais montant, se voient ici et nulle part
     * ailleurs.
     *
     * Ne retient que les dossiers qui ont un décaissement ou une facture:
     * les autres n'ont rien à rapprocher.
     *
     * @return list<array<string, mixed>>
     */
    public function reconciliation(): array
    {
        $debours = config(Invoicing::class)->deboursSql();

        return $this->select(
            "`transit_folders`.`id`, `transit_folders`.`bl`, `transit_folders`.`invoice_to`,"
            . " `transit_folders`.`invoiced`, `transit_folders`.`closed`,"
            . " `transit_folders`.`invoice_date`, `transit_folders`.`invoice_amount`,"
            . " (" . $debours . ") AS facture_debours,"
            . " COALESCE(d.`total`, 0) AS decaisse,"
            . " (" . $debours . ") - COALESCE(d.`total`, 0) AS ecart",
            false
        )
            // Sous-requête agrégée plutôt qu'une corrélée par ligne: le
            // rapprochement doit rester consultable sur tout le portefeuille.
            ->join(
                "(SELECT `tenant_id`, `folder_id`, SUM(`amount`) AS total"
                . " FROM `cash_movements`"
                . " WHERE `kind` = 'depense' AND `folder_id` IS NOT NULL AND `deleted_at` IS NULL"
                . " GROUP BY `tenant_id`, `folder_id`) d",
                "d.`tenant_id` = `transit_folders`.`tenant_id` AND d.`folder_id` = `transit_folders`.`id`",
                "left",
                false
            )
            ->where("(d.`total` IS NOT NULL OR `transit_folders`.`invoiced` = 1)", null, false)
            ->orderBy("ecart", "asc")
            ->findAll();
    }

    /**
     * Encours par client: factures émises et non encaissées.
     *
     * Sépare le total facturé de la part de débours — la trésorerie
     * réellement sortie — et ventile par antériorité. Tout est agrégé en une
     * requête: la balance âgée d'une maison de transit se consulte souvent,
     * elle ne doit pas coûter un parcours de table.
     *
     * @return list<array<string, mixed>>
     */
    public function outstandingByClient(): array
    {
        $invoicing = config(Invoicing::class);

        // Le solde, pas le montant facturé: un acompte déjà versé n'est plus
        // de la trésorerie à récupérer.
        $solde = "(`invoice_amount` - `paid_amount`)";

        // Les règlements partiels ne s'imputent pas poste par poste. La part
        // de débours restant due est donc calculée au prorata du solde —
        // convention explicable, à défaut d'une imputation que le métier ne
        // fournit pas.
        $deboursRestants = "(" . $invoicing->deboursSql() . ") * " . $solde
            . " / NULLIF(`invoice_amount`, 0)";

        $select = "`invoice_to` AS client_id,"
            . " COUNT(*) AS factures,"
            . " SUM(" . $solde . ") AS encours,"
            . " SUM(`paid_amount`) AS deja_regle,"
            . " SUM(COALESCE(" . $deboursRestants . ", 0)) AS debours,"
            . " SUM(CASE WHEN `paid_amount` > 0 THEN 1 ELSE 0 END) AS factures_entamees,"
            . " MIN(`invoice_date`) AS plus_ancienne,"
            . " MAX(DATEDIFF(CURDATE(), `invoice_date`)) AS jours_max";

        foreach ($invoicing->aging as $cle => $tranche) {
            // Les clés viennent de la configuration, mais elles deviennent
            // des alias SQL: on refuse tout ce qui n'est pas un identifiant.
            if (preg_match("/^[a-z0-9_]+$/", (string) $cle) !== 1) {
                continue;
            }

            $condition = $tranche["to"] === null
                ? "DATEDIFF(CURDATE(), `invoice_date`) >= " . (int) $tranche["from"]
                : "DATEDIFF(CURDATE(), `invoice_date`) BETWEEN " . (int) $tranche["from"] . " AND " . (int) $tranche["to"];

            $select .= ", SUM(CASE WHEN " . $condition . " THEN " . $solde . " ELSE 0 END) AS " . $cle;
        }

        return $this->select($select, false)
            ->where("invoiced", true)
            ->where("`paid_amount` < `invoice_amount`", null, false)
            ->groupBy("invoice_to")
            ->orderBy("encours", "desc")
            ->findAll();
    }

    /**
     * Expression SQL donnant l'étape courante d'un dossier.
     *
     * L'ordre des branches va de la plus avancée à la plus précoce: la
     * première vraie l'emporte. L'étape se déduit des dates déjà saisies,
     * elle n'est donc jamais à ressaisir ni à resynchroniser.
     */
    private static function stageSql(): string
    {
        return "CASE"
            . " WHEN `invoiced` = 1 AND `paid_amount` >= `invoice_amount` THEN 'a_cloturer'"
            . " WHEN `invoiced` = 1 THEN 'a_encaisser'"
            . " WHEN `delivery_date` IS NOT NULL AND `invoiced` = 0 THEN 'a_facturer'"
            . " WHEN `bae_date` IS NOT NULL AND `delivery_date` IS NULL THEN 'a_livrer'"
            . " WHEN `customs_admission_date` IS NOT NULL AND `bae_date` IS NULL THEN 'en_douane'"
            . " WHEN `transit_order_date` IS NOT NULL AND `customs_admission_date` IS NULL THEN 'a_declarer'"
            . " ELSE 'ouvert' END";
    }

    /** Date d'entrée dans l'étape courante, mêmes branches que stageSql(). */
    private static function stageSinceSql(): string
    {
        return "CASE"
            . " WHEN `invoiced` = 1 AND `paid_amount` >= `invoice_amount` THEN `invoice_date`"
            . " WHEN `invoiced` = 1 THEN `invoice_date`"
            . " WHEN `delivery_date` IS NOT NULL AND `invoiced` = 0 THEN `delivery_date`"
            . " WHEN `bae_date` IS NOT NULL AND `delivery_date` IS NULL THEN `bae_date`"
            . " WHEN `customs_admission_date` IS NOT NULL AND `bae_date` IS NULL THEN `customs_admission_date`"
            . " WHEN `transit_order_date` IS NOT NULL AND `customs_admission_date` IS NULL THEN `transit_order_date`"
            . " ELSE `open_date` END";
    }

    /**
     * Nombre de dossiers en cours par étape.
     *
     * @return array<string, int>
     */
    public function stageCounts(): array
    {
        $rows = $this->select(self::stageSql() . " AS stage, COUNT(*) AS total", false)
            ->where("closed", false)
            ->groupBy("stage")
            ->findAll();

        return array_map("intval", array_column($rows, "total", "stage"));
    }

    /**
     * Dossiers en cours, avec leur étape et le temps passé dedans.
     *
     * Les dossiers clos sont exclus: le suivi d'exploitation ne porte que sur
     * ce qui demande encore une action.
     *
     * @return list<array<string, mixed>>
     */
    public function tracked(): array
    {
        return $this->select(
            "`transit_folders`.*,"
            . " " . self::stageSql() . " AS stage,"
            . " " . self::stageSinceSql() . " AS stage_since,"
            . " DATEDIFF(CURDATE(), " . self::stageSinceSql() . ") AS stage_days",
            false
        )
            ->where("closed", false)
            ->orderBy("stage_days", "desc")
            ->findAll();
    }

    /**
     * Chiffre d'affaires facturé, par mois, pour une année.
     *
     * Une requête groupée au lieu de douze appels successifs qui chargeaient
     * chacun toutes les factures du mois pour les additionner en PHP.
     *
     * @return array<int, float> Indexé de 1 à 12, les mois sans facture à 0.
     */
    public function monthlySales(int $year): array
    {
        return array_column($this->monthlyTurnover($year), "facture");
    }

    /**
     * Facturé et produit, par mois.
     *
     * Les deux ne se confondent pas: invoice_amount additionne les
     * trente-trois postes, débours compris. L'essentiel de ce montant est de
     * l'argent avancé pour le compte du client, qui ne fait que transiter.
     * Le produit de la maison, c'est sa rémunération.
     *
     * @return array<int, array{facture: float, produit: float}> Indexé de 1 à 12.
     */
    public function monthlyTurnover(int $year): array
    {
        $invoicing = config(Invoicing::class);

        $rows = $this->select(
            "MONTH(`invoice_date`) AS mois,"
            . " SUM(`invoice_amount`) AS facture,"
            . " SUM(" . $invoicing->remunerationSql() . ") AS produit",
            false
        )
            ->where("closed", true)
            ->where("YEAR(invoice_date)", $year)
            ->groupBy("MONTH(invoice_date)")
            ->findAll();

        $parMois = array_fill(1, 12, ["facture" => 0.0, "produit" => 0.0]);

        foreach ($rows as $row) {
            $parMois[(int) $row["mois"]] = [
                "facture" => (float) $row["facture"],
                "produit" => (float) $row["produit"],
            ];
        }

        return $parMois;
    }

    /**
     * Facturé et produit sur une période.
     *
     * @return array{facture: float, produit: float}
     */
    public function turnoverBetween(string $from, string $to): array
    {
        $invoicing = config(Invoicing::class);

        $row = $this->select(
            "SUM(`invoice_amount`) AS facture,"
            . " SUM(" . $invoicing->remunerationSql() . ") AS produit",
            false
        )
            ->where("closed", true)
            ->where("invoice_date >=", $from)
            ->where("invoice_date <=", $to)
            ->first();

        return [
            "facture" => (float) ($row["facture"] ?? 0),
            "produit" => (float) ($row["produit"] ?? 0),
        ];
    }

    /**
     * Complète les dossiers avec leurs relations.
     *
     * Le nombre de requêtes est fixe, quel que soit le nombre de dossiers.
     * L'implémentation précédente interrogeait la base quatre fois par
     * dossier — colis, client, auteur de facturation, pièces jointes — ce qui
     * coûtait plus de mille requêtes et dix secondes pour trois cents lignes.
     */
    protected function getFolderItems($folder)
    {
        if (empty($folder["data"])) {
            return $folder;
        }

        // singleton distingue find($id) et first() d'un findAll(). Plus
        // fiable que l'ancien test sur $folder["id"], qui prenait une
        // recherche par tableau d'identifiants pour un dossier unique.
        $single = !empty($folder["singleton"]);
        $rows = $single ? [$folder["data"]] : $folder["data"];

        // Une requête d'agrégat (SUM, COUNT groupé) renvoie des lignes sans
        // identifiant: il n'y a aucune relation à y rattacher, et tenter de
        // le faire lèverait une erreur sur une clé absente.
        if (!array_key_exists("id", $rows[0] ?? [])) {
            return $folder;
        }

        $rows = $this->attachRelations($rows);

        $folder["data"] = $single ? $rows[0] : $rows;

        return $folder;
    }

    /**
     * Charge les relations de tous les dossiers en quatre requêtes, puis les
     * répartit en mémoire.
     *
     * @param  list<array<string, mixed>> $rows
     * @return list<array<string, mixed>>
     */
    private function attachRelations(array $rows): array
    {
        $folderIds = array_column($rows, "id");
        $clientIds = array_values(array_unique(array_filter(array_column($rows, "invoice_to"))));
        $authorIds = array_values(array_unique(array_filter(array_column($rows, "invoice_author"))));

        // Les modèles restent cloisonnés: whereIn passe par beforeFind, donc
        // par le filtre sur l'agence.
        $items = $this->groupByFolder(
            (new TransitFolderItems())->whereIn("folder_id", $folderIds)->findAll()
        );
        $files = $this->groupByFolder(
            (new TransitFiles())->whereIn("folder_id", $folderIds)->findAll()
        );

        $clients = $clientIds === []
            ? []
            : array_column((new Clients())->whereIn("id", $clientIds)->findAll(), null, "id");
        $authors = $authorIds === []
            ? []
            : array_column((new Users())->whereIn("id", $authorIds)->findAll(), null, "id");

        foreach ($rows as &$row) {
            $id = $row["id"];

            $row["items"] = $items[$id] ?? [];
            $row["files"] = $files[$id] ?? [];

            // Un client ou un auteur absent de la table — supprimé, ou jamais
            // renseigné — garde la forme attendue par les vues.
            $row["invoice_to"] = empty($row["invoice_to"])
                ? $this->unknownRelated()
                : ($clients[$row["invoice_to"]] ?? $this->unknownRelated());
            $row["invoice_author"] = empty($row["invoice_author"])
                ? $this->unknownRelated()
                : ($authors[$row["invoice_author"]] ?? $this->unknownRelated());

            // invoice_amount est désormais une colonne générée par la base:
            // les trente-trois postes ne sont plus additionnés ici.
            $row["items_count"] = 0;
            $row["total_weight"] = 0;
            foreach ($row["items"] as $item) {
                $row["items_count"] += $item["quantity"];
                $row["total_weight"] += $item["weight"];
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>> $rows
     * @return array<int|string, list<array<string, mixed>>>
     */
    private function groupByFolder(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row["folder_id"]][] = $row;
        }

        return $grouped;
    }

    /** Forme de repli attendue par les vues pour une relation absente. */
    private function unknownRelated(): array
    {
        return [
            "id" => null,
            "name" => "INFORMATIONS INDISPONIBLES",
            "email" => null,
        ];
    }
}
