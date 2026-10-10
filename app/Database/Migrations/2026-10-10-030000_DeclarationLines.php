<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Note de détail: ventilation d'un envoi en lignes tarifaires.
 *
 * C'est le document de référence à partir duquel la déclaration est saisie
 * dans GAINDE. Chaque ligne porte un code tarifaire et sa chaîne de valeur
 * FOB, fret, assurance, CAF — l'assiette des droits.
 *
 * Volontairement distincte de folder_items: les colis décrivent
 * l'emballage, les lignes tarifaires décrivent la valeur. Une ligne peut
 * couvrir des centaines de colis, et les deux ventilations ne se
 * correspondent pas.
 *
 * Un seul poids par ligne: le formulaire papier comporte deux colonnes,
 * brut et net, mais la même valeur y est reportée deux fois. L'impression
 * duplique, la saisie ne le fait pas.
 */
class DeclarationLines extends Migration
{
    public function up()
    {
        // En-tête de la note, absent jusqu'ici du dossier.
        $this->forge->addColumn("transit_folders", [
            "provenance" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
                "after" => "expeditor",
            ],
            "customs_regime" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
                "after" => "declaration",
            ],
            "agreement_number" => [
                "type" => "VARCHAR",
                "constraint" => 100,
                "null" => true,
                "after" => "customs_regime",
            ],
        ]);

        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "tenant_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => false],
            "folder_id" => ["type" => "BIGINT", "constraint" => 20, "unsigned" => true, "null" => false],
            "line_no" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "default" => 1],
            "hs_code" => ["type" => "VARCHAR", "constraint" => 20, "null" => true],
            "description" => ["type" => "VARCHAR", "constraint" => 255, "null" => true],
            "origin" => ["type" => "VARCHAR", "constraint" => 10, "null" => true],
            "weight" => ["type" => "DOUBLE", "null" => true],
            "fob_value" => ["type" => "DOUBLE", "null" => true],
            "freight_value" => ["type" => "DOUBLE", "null" => true],
            // Facultative: toutes les marchandises ne sont pas assurées.
            "insurance_value" => ["type" => "DOUBLE", "null" => true],
            // Saisi, non calculé: le document douanier fait foi, et les
            // déclarants arrondissent. Le logiciel signale l'écart sans le
            // corriger.
            "caf_value" => ["type" => "DOUBLE", "null" => true],
            "complementary_quantity" => ["type" => "VARCHAR", "constraint" => 50, "null" => true],
            "container_chassis" => ["type" => "VARCHAR", "constraint" => 100, "null" => true],
            "reference" => ["type" => "VARCHAR", "constraint" => 100, "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
            "deleted_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("declaration_lines");

        $this->db->query(
            "ALTER TABLE `declaration_lines` ADD KEY `declaration_lines_tenant_folder` (`tenant_id`, `folder_id`)"
        );
        $this->db->query(
            "ALTER TABLE `declaration_lines` ADD CONSTRAINT `declaration_lines_tenant_folder_foreign`"
            . " FOREIGN KEY (`tenant_id`, `folder_id`) REFERENCES `transit_folders` (`tenant_id`, `id`)"
            . " ON DELETE CASCADE ON UPDATE CASCADE"
        );
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `declaration_lines` DROP FOREIGN KEY `declaration_lines_tenant_folder_foreign`");
        $this->forge->dropTable("declaration_lines");
        $this->forge->dropColumn("transit_folders", ["provenance", "customs_regime", "agreement_number"]);
    }
}
