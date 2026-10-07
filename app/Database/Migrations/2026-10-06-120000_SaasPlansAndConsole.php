<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 3: offres, quotas et console d'exploitation.
 *
 * Trois ajouts:
 *  - une table d'offres, avec NULL signifiant « illimité » plutôt qu'un
 *    nombre arbitrairement grand;
 *  - le rattachement de chaque agence à une offre;
 *  - un drapeau d'administrateur de plateforme sur les comptes.
 *
 * Le drapeau est porté par users plutôt que par une table d'authentification
 * séparée: les exploitants de la plateforme réutilisent la connexion
 * existante, et la console travaille explicitement hors cloisonnement. Un
 * second système d'authentification aurait doublé la surface d'attaque pour
 * un gain nul à ce stade.
 *
 * transit_files reçoit une colonne size: le quota de stockage se calcule par
 * somme en base, sans parcourir le disque à chaque vérification.
 */
class SaasPlansAndConsole extends Migration
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
            "name" => [
                "type" => "VARCHAR",
                "constraint" => 255,
            ],
            "slug" => [
                "type" => "VARCHAR",
                "constraint" => 100,
            ],
            // NULL = illimité, sur les trois quotas.
            "max_users" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => true,
            ],
            "max_folders_per_month" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => true,
            ],
            "max_storage_mb" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => true,
            ],
            "price_cfa" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "default" => 0,
            ],
            "active" => [
                "type" => "TINYINT",
                "constraint" => 1,
                "default" => 1,
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
        $this->forge->addUniqueKey("slug");
        $this->forge->createTable("plans");

        $now = date("Y-m-d H:i:s");
        $this->db->table("plans")->insertBatch([
            [
                "name" => "Découverte",
                "slug" => "decouverte",
                "max_users" => 3,
                "max_folders_per_month" => 20,
                "max_storage_mb" => 200,
                "price_cfa" => 0,
                "active" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "name" => "Agence",
                "slug" => "agence",
                "max_users" => 15,
                "max_folders_per_month" => 300,
                "max_storage_mb" => 5000,
                "price_cfa" => 45000,
                "active" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
            [
                "name" => "Illimité",
                "slug" => "illimite",
                "max_users" => null,
                "max_folders_per_month" => null,
                "max_storage_mb" => null,
                "price_cfa" => 150000,
                "active" => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ],
        ]);

        $this->forge->addColumn("tenants", [
            "plan_id" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => true,
                "after" => "slug",
            ],
        ]);

        $this->forge->addColumn("users", [
            "is_platform_admin" => [
                "type" => "TINYINT",
                "constraint" => 1,
                "null" => false,
                "default" => 0,
                "after" => "profile",
            ],
        ]);

        $this->forge->addColumn("transit_files", [
            "size" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => false,
                "default" => 0,
                "after" => "path",
            ],
        ]);

        $this->db->query(
            "ALTER TABLE `tenants` ADD CONSTRAINT `tenants_plan_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE"
        );

        // Les agences existantes partent sur l'offre intermédiaire: les
        // basculer d'office sur l'offre gratuite les mettrait hors quota.
        $agence = $this->db->table("plans")->where("slug", "agence")->get()->getRowArray();
        if ($agence !== null) {
            $this->db->table("tenants")->where("plan_id", null)->update(["plan_id" => $agence["id"]]);
        }

        $this->backfillFileSizes();
    }

    /**
     * Renseigne la taille des pièces jointes déjà stockées.
     *
     * Sans ce rattrapage, le quota de stockage démarrerait à zéro pour les
     * agences existantes et laisserait passer un dépassement silencieux.
     */
    private function backfillFileSizes(): void
    {
        $rows = $this->db->table("transit_files")
            ->select("id, path")
            ->where("size", 0)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            if (empty($row["path"])) {
                continue;
            }

            $absolute = WRITEPATH . "uploads/" . $row["path"];

            if (is_file($absolute)) {
                $this->db->table("transit_files")
                    ->where("id", $row["id"])
                    ->update(["size" => filesize($absolute)]);
            }
        }
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `tenants` DROP FOREIGN KEY `tenants_plan_foreign`");
        $this->forge->dropColumn("tenants", "plan_id");
        $this->forge->dropColumn("users", "is_platform_admin");
        $this->forge->dropColumn("transit_files", "size");
        $this->forge->dropTable("plans");
    }
}
