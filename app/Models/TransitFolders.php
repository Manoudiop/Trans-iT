<?php

namespace App\Models;

class TransitFolders extends TenantModel
{
    protected $table            = 'transit_folders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
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
    protected $useTimestamps = false;
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
     * Chiffre d'affaires facturé, par mois, pour une année.
     *
     * Une requête groupée au lieu de douze appels successifs qui chargeaient
     * chacun toutes les factures du mois pour les additionner en PHP.
     *
     * @return array<int, float> Indexé de 1 à 12, les mois sans facture à 0.
     */
    public function monthlySales(int $year): array
    {
        $rows = $this->select("MONTH(invoice_date) AS mois, SUM(invoice_amount) AS total")
            ->where("closed", true)
            ->where("YEAR(invoice_date)", $year)
            ->groupBy("MONTH(invoice_date)")
            ->findAll();

        $parMois = array_fill(1, 12, 0.0);

        foreach ($rows as $row) {
            $parMois[(int) $row["mois"]] = (float) $row["total"];
        }

        return $parMois;
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
