<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dette 2/3: horodatage et suppression logique des dossiers de transit.
 *
 * transit_folders était la seule table métier sans created_at, updated_at ni
 * deleted_at. Un dossier facturé supprimé disparaissait définitivement, avec
 * ses colis et ses pièces jointes emportés par les clés étrangères en
 * CASCADE — difficilement défendable sur des pièces comptables.
 *
 * Conséquence à connaître: les index uniques (tenant_id, bl) et
 * (tenant_id, reference) ne distinguent pas une ligne supprimée. Un dossier
 * mis à la corbeille continue donc d'occuper son numéro de connaissement.
 * C'est volontaire — un connaissement reste attaché à un dossier existant —
 * et c'est pourquoi la corbeille permet de restaurer plutôt que de ressaisir.
 */
class FolderTimestampsAndSoftDeletes extends Migration
{
    public function up()
    {
        $this->forge->addColumn("transit_folders", [
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

        // Les dossiers existants n'ont pas de date de création réelle:
        // open_date est le seul signal disponible, et c'est la date métier
        // qui fait foi pour un dossier de transit.
        $this->db->query(
            "UPDATE `transit_folders` SET `created_at` = `open_date`, `updated_at` = `open_date`"
            . " WHERE `created_at` IS NULL AND `open_date` IS NOT NULL"
        );

        // Les listes filtrent systématiquement sur deleted_at.
        $this->db->query(
            "ALTER TABLE `transit_folders` ADD KEY `transit_folders_tenant_deleted` (`tenant_id`, `deleted_at`)"
        );
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `transit_folders` DROP INDEX `transit_folders_tenant_deleted`");
        $this->forge->dropColumn("transit_folders", ["created_at", "updated_at", "deleted_at"]);
    }
}
