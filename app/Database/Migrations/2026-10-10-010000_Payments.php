<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Règlements partiels.
 *
 * Jusqu'ici une facture était encaissée ou ne l'était pas, selon que
 * receipt_date ou check_date était renseignée. Sur de gros dossiers, les
 * clients règlent en plusieurs fois: l'encours affiché était donc surestimé,
 * un acompte de huit millions sur dix ne se voyant nulle part.
 *
 * transit_folders reçoit un paid_amount dénormalisé, recalculé par le modèle
 * Payments à chaque écriture. Il aurait été possible de sommer les
 * règlements à la volée, mais le montant encaissé entre dans l'étape du
 * dossier et dans la balance âgée, deux requêtes agrégées fréquentes: le
 * garder sur la ligne évite une jointure partout.
 *
 * Les colonnes receipt / check sont conservées: elles portent les références
 * des pièces justificatives, qui restent affichées sur le dossier et sur
 * l'impression.
 */
class Payments extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "auto_increment" => true,
            ],
            "tenant_id" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => false,
            ],
            "folder_id" => [
                "type" => "BIGINT",
                "constraint" => 20,
                "unsigned" => true,
                "null" => false,
            ],
            "amount" => [
                "type" => "DOUBLE",
                "null" => false,
                "default" => 0,
            ],
            "paid_at" => [
                "type" => "DATE",
                "null" => true,
            ],
            "method" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
            ],
            "reference" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
            ],
            "recorded_by" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => true,
            ],
            "note" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
            ],
            "created_at" => [
                "type" => "DATETIME",
                "null" => true,
            ],
            "updated_at" => [
                "type" => "DATETIME",
                "null" => true,
            ],
            "deleted_at" => [
                "type" => "DATETIME",
                "null" => true,
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("payments");

        // Clé étrangère composite, comme partout ailleurs: un règlement ne
        // peut pas pointer vers le dossier d'une autre agence.
        $this->db->query(
            "ALTER TABLE `payments` ADD CONSTRAINT `payments_tenant_folder_foreign`"
            . " FOREIGN KEY (`tenant_id`, `folder_id`) REFERENCES `transit_folders` (`tenant_id`, `id`)"
            . " ON DELETE CASCADE ON UPDATE CASCADE"
        );
        $this->db->query(
            "ALTER TABLE `payments` ADD KEY `payments_tenant_paid_at` (`tenant_id`, `paid_at`)"
        );

        $this->forge->addColumn("transit_folders", [
            "paid_amount" => [
                "type" => "DOUBLE",
                "null" => false,
                "default" => 0,
                "after" => "invoiced",
            ],
        ]);

        // Reprise de l'existant: une facture marquée encaissée devient un
        // règlement unique du montant total, daté de la pièce. Sans cette
        // reprise, tout l'historique réglé réapparaîtrait comme impayé.
        $this->db->query(
            "INSERT INTO `payments` (`tenant_id`, `folder_id`, `amount`, `paid_at`, `method`, `reference`, `created_at`, `updated_at`)"
            . " SELECT `tenant_id`, `id`, `invoice_amount`, COALESCE(`receipt_date`, `check_date`),"
            . " CASE WHEN `check_date` IS NOT NULL THEN 'cheque' ELSE 'recu' END,"
            . " COALESCE(NULLIF(`check`, ''), NULLIF(`receipt`, '')), NOW(), NOW()"
            . " FROM `transit_folders`"
            . " WHERE `invoiced` = 1 AND (`receipt_date` IS NOT NULL OR `check_date` IS NOT NULL)"
            . " AND `invoice_amount` > 0"
        );

        $this->db->query(
            "UPDATE `transit_folders` f SET `paid_amount` = COALESCE("
            . "(SELECT SUM(p.`amount`) FROM `payments` p"
            . " WHERE p.`tenant_id` = f.`tenant_id` AND p.`folder_id` = f.`id` AND p.`deleted_at` IS NULL), 0)"
        );
    }

    public function down()
    {
        $this->forge->dropColumn("transit_folders", "paid_amount");
        $this->db->query("ALTER TABLE `payments` DROP FOREIGN KEY `payments_tenant_folder_foreign`");
        $this->forge->dropTable("payments");
    }
}
