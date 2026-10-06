<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 1 de la migration SaaS: cloisonnement par tenant en base partagée.
 *
 * Trois partis pris, documentés ici parce qu'ils conditionnent tout le reste:
 *
 * 1. Les identifiants métier (numéro de dossier, code client) sont saisis ou
 *    générés par l'agence: ils ne peuvent pas être uniques globalement. Les
 *    clés primaires de clients et transit_folders deviennent donc composites
 *    (tenant_id, id), ce qui donne à chaque agence sa propre numérotation.
 *
 * 2. Les clés étrangères deviennent composites elles aussi. C'est le gain le
 *    plus important: référencer le dossier ou le client d'une autre agence
 *    devient impossible au niveau du moteur, pas seulement applicatif.
 *
 * 3. users.email reste unique GLOBALEMENT. La connexion se fait par email
 *    seul; rendre l'email unique par tenant la rendrait ambiguë. Un compte
 *    appartient donc à une agence et une seule. Passer à une résolution par
 *    sous-domaine lèvera cette contrainte plus tard.
 */
class MultiTenant extends Migration
{
    /** Tables métier à cloisonner. */
    private array $tables = [
        "users",
        "clients",
        "transit_folders",
        "folder_items",
        "transit_files",
    ];

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
        $this->forge->createTable("tenants");

        // Agence de départ: tout l'existant lui est rattaché.
        $now = date("Y-m-d H:i:s");
        $this->db->table("tenants")->insert([
            "name" => "Agence principale",
            "slug" => "principal",
            "active" => 1,
            "created_at" => $now,
            "updated_at" => $now,
        ]);

        // DEFAULT 1 reprend les lignes existantes sans requête de rattrapage;
        // il est retiré en fin de migration pour qu'aucune écriture ne puisse
        // plus atterrir dans l'agence 1 par omission.
        foreach ($this->tables as $table) {
            $this->forge->addColumn($table, [
                "tenant_id" => [
                    "type" => "INT",
                    "constraint" => 11,
                    "unsigned" => true,
                    "null" => false,
                    "default" => 1,
                    "after" => "id",
                ],
            ]);
        }

        foreach ($this->statements() as $sql) {
            $this->db->query($sql);
        }

        foreach ($this->tables as $table) {
            $this->db->query("ALTER TABLE `{$table}` ALTER COLUMN `tenant_id` DROP DEFAULT");
        }
    }

    /**
     * Chirurgie des index, clés primaires et clés étrangères.
     *
     * L'ordre compte: les clés étrangères doivent tomber avant les clés
     * primaires qu'elles référencent, et être recréées après.
     */
    private function statements(): array
    {
        return [
            // 1. Libérer les clés primaires référencées.
            "ALTER TABLE `transit_folders` DROP FOREIGN KEY `transit_folders_invoice_to_foreign`",
            "ALTER TABLE `folder_items` DROP FOREIGN KEY `folder_items_folder_id_foreign`",
            "ALTER TABLE `transit_files` DROP FOREIGN KEY `transit_files_folder_id_foreign`",

            // 2. clients: clé primaire composite, unicités par agence.
            //    L'index UNIQUE(id) doublonnait déjà la clé primaire.
            "ALTER TABLE `clients` DROP INDEX `id`",
            "ALTER TABLE `clients` DROP PRIMARY KEY, ADD PRIMARY KEY (`tenant_id`, `id`)",
            "ALTER TABLE `clients` DROP INDEX `account_number`, ADD UNIQUE KEY `clients_tenant_account_number` (`tenant_id`, `account_number`)",
            "ALTER TABLE `clients` DROP INDEX `email`, ADD UNIQUE KEY `clients_tenant_email` (`tenant_id`, `email`)",
            "ALTER TABLE `clients` DROP INDEX `phone`, ADD UNIQUE KEY `clients_tenant_phone` (`tenant_id`, `phone`)",

            // 3. transit_folders: idem. Le numéro de dossier et le numéro de
            //    connaissement redeviennent réutilisables d'une agence à
            //    l'autre, ce qui est le cas réel du métier.
            "ALTER TABLE `transit_folders` DROP INDEX `id`",
            "ALTER TABLE `transit_folders` DROP PRIMARY KEY, ADD PRIMARY KEY (`tenant_id`, `id`)",
            "ALTER TABLE `transit_folders` DROP INDEX `bl`, ADD UNIQUE KEY `transit_folders_tenant_bl` (`tenant_id`, `bl`)",
            "ALTER TABLE `transit_folders` DROP INDEX `reference`, ADD UNIQUE KEY `transit_folders_tenant_reference` (`tenant_id`, `reference`)",

            // 4. users: email volontairement unique globalement (cf. en-tête).
            "ALTER TABLE `users` ADD KEY `users_tenant_id` (`tenant_id`)",

            // 5. folder_items et transit_files: clés étrangères composites.
            //    Un colis ou une pièce jointe ne peut plus pointer vers le
            //    dossier d'une autre agence.
            "ALTER TABLE `folder_items` ADD CONSTRAINT `folder_items_tenant_folder_foreign` FOREIGN KEY (`tenant_id`, `folder_id`) REFERENCES `transit_folders` (`tenant_id`, `id`) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE `transit_files` ADD UNIQUE KEY `transit_files_tenant_path` (`tenant_id`, `path`)",
            "ALTER TABLE `transit_files` ADD CONSTRAINT `transit_files_tenant_folder_foreign` FOREIGN KEY (`tenant_id`, `folder_id`) REFERENCES `transit_folders` (`tenant_id`, `id`) ON DELETE CASCADE ON UPDATE CASCADE",

            // 6. transit_folders -> clients, composite.
            //    RESTRICT remplace l'ancien SET NULL: une clé étrangère
            //    composite ne peut pas être mise à NULL partiellement, et
            //    refuser la suppression d'un client encore rattaché à des
            //    dossiers facturés est de toute façon le bon comportement.
            "ALTER TABLE `transit_folders` ADD CONSTRAINT `transit_folders_tenant_client_foreign` FOREIGN KEY (`tenant_id`, `invoice_to`) REFERENCES `clients` (`tenant_id`, `id`) ON DELETE RESTRICT ON UPDATE CASCADE",
        ];
    }

    public function down()
    {
        $reverse = [
            "ALTER TABLE `transit_folders` DROP FOREIGN KEY `transit_folders_tenant_client_foreign`",
            "ALTER TABLE `transit_files` DROP FOREIGN KEY `transit_files_tenant_folder_foreign`",
            "ALTER TABLE `transit_files` DROP INDEX `transit_files_tenant_path`",
            "ALTER TABLE `folder_items` DROP FOREIGN KEY `folder_items_tenant_folder_foreign`",
            "ALTER TABLE `users` DROP INDEX `users_tenant_id`",

            "ALTER TABLE `transit_folders` DROP INDEX `transit_folders_tenant_reference`, ADD UNIQUE KEY `reference` (`reference`)",
            "ALTER TABLE `transit_folders` DROP INDEX `transit_folders_tenant_bl`, ADD UNIQUE KEY `bl` (`bl`)",
            "ALTER TABLE `transit_folders` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)",
            "ALTER TABLE `transit_folders` ADD UNIQUE KEY `id` (`id`)",

            "ALTER TABLE `clients` DROP INDEX `clients_tenant_phone`, ADD UNIQUE KEY `phone` (`phone`)",
            "ALTER TABLE `clients` DROP INDEX `clients_tenant_email`, ADD UNIQUE KEY `email` (`email`)",
            "ALTER TABLE `clients` DROP INDEX `clients_tenant_account_number`, ADD UNIQUE KEY `account_number` (`account_number`)",
            "ALTER TABLE `clients` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)",
            "ALTER TABLE `clients` ADD UNIQUE KEY `id` (`id`)",

            "ALTER TABLE `transit_folders` ADD CONSTRAINT `transit_folders_invoice_to_foreign` FOREIGN KEY (`invoice_to`) REFERENCES `clients` (`id`) ON DELETE SET NULL ON UPDATE CASCADE",
            "ALTER TABLE `folder_items` ADD CONSTRAINT `folder_items_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `transit_folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE",
            "ALTER TABLE `transit_files` ADD CONSTRAINT `transit_files_folder_id_foreign` FOREIGN KEY (`folder_id`) REFERENCES `transit_folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE",
        ];

        foreach ($reverse as $sql) {
            $this->db->query($sql);
        }

        foreach ($this->tables as $table) {
            $this->forge->dropColumn($table, "tenant_id");
        }

        $this->forge->dropTable("tenants");
    }
}
