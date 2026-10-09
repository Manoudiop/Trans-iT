<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 4: le montant de facture devient une colonne, et les index manquants
 * sont posés.
 *
 * invoice_amount était additionné en PHP à partir de trente-trois colonnes,
 * sur chaque ligne de chaque liste. Conséquences: impossible de le sommer en
 * SQL, donc getSalesFigures() chargeait toutes les factures d'un mois pour en
 * faire le total en mémoire; et impossible de trier ou filtrer dessus.
 *
 * En colonne générée STORED, le moteur la calcule à l'écriture: SUM() et
 * ORDER BY redeviennent possibles, et l'application n'a plus rien à
 * maintenir. COALESCE est indispensable, les trente-trois postes étant
 * nullables — en SQL, NULL + 1 vaut NULL, là où PHP comptait zéro.
 */
class InvoiceAmountAndIndexes extends Migration
{
    private array $fees = [
        "duties_taxes", "agios", "bl_stamp", "shipping_taxe",
        "boarding_disembarkation", "storing_guarding", "container_transportation",
        "handling", "insurance", "transportation", "expert_report",
        "customs_excort", "demurrage", "customs_clearance",
        "postal_package_withdrawal_fees", "customs_ts_visit", "full_land_rental",
        "visit_admissibility", "indirect_fees", "orbus_fees", "trucking",
        "grouping", "commission_on_disbursements", "folder_opening_fees",
        "transit_commission", "customs_honorary_fees", "had",
        "internal_handling", "loading_unloading", "printer",
        "procedures_formalities", "tps", "freight",
    ];

    public function up()
    {
        $terms = array_map(
            static fn (string $fee): string => "COALESCE(`" . $fee . "`, 0)",
            $this->fees
        );

        $this->db->query(
            "ALTER TABLE `transit_folders` ADD COLUMN `invoice_amount` DOUBLE"
            . " GENERATED ALWAYS AS (" . implode(" + ", $terms) . ") STORED"
        );

        foreach ($this->indexes() as $name => $sql) {
            $this->db->query($sql);
        }
    }

    /**
     * Index dictés par les requêtes réellement exécutées.
     *
     * tenant_id est en tête de chaque index: toute requête de l'application
     * est cloisonnée, donc le filtre sur l'agence est toujours présent.
     */
    private function indexes(): array
    {
        return [
            "transit_folders_tenant_invoiced" => "ALTER TABLE `transit_folders` ADD KEY `transit_folders_tenant_invoiced` (`tenant_id`, `invoiced`)",
            "transit_folders_tenant_closed" => "ALTER TABLE `transit_folders` ADD KEY `transit_folders_tenant_closed` (`tenant_id`, `closed`)",
            "transit_folders_tenant_open_date" => "ALTER TABLE `transit_folders` ADD KEY `transit_folders_tenant_open_date` (`tenant_id`, `open_date`)",
            "transit_folders_tenant_invoice_date" => "ALTER TABLE `transit_folders` ADD KEY `transit_folders_tenant_invoice_date` (`tenant_id`, `invoice_date`)",
            "clients_tenant_created_at" => "ALTER TABLE `clients` ADD KEY `clients_tenant_created_at` (`tenant_id`, `created_at`)",
        ];
    }

    public function down()
    {
        foreach (array_keys($this->indexes()) as $name) {
            $table = str_starts_with($name, "clients") ? "clients" : "transit_folders";
            $this->db->query("ALTER TABLE `" . $table . "` DROP INDEX `" . $name . "`");
        }

        $this->db->query("ALTER TABLE `transit_folders` DROP COLUMN `invoice_amount`");
    }
}
