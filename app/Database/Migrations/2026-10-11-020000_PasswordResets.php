<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Jetons de réinitialisation de mot de passe.
 *
 * Seul le haché du jeton est stocké, jamais le jeton lui-même: une copie de
 * la base ne permet donc pas de prendre la main sur les comptes, exactement
 * comme pour les mots de passe.
 *
 * La ligne porte tenant_id bien que user_id suffise à l'identifier: le
 * contexte d'agence doit être posé avant d'écrire le nouveau mot de passe,
 * et le lire ici évite une jointure sur users à ce moment précis.
 */
class PasswordResets extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "tenant_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => false],
            "user_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => false],
            "token_hash" => ["type" => "CHAR", "constraint" => 64, "null" => false],
            "expires_at" => ["type" => "DATETIME", "null" => false],
            "used_at" => ["type" => "DATETIME", "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addPrimaryKey("id");
        // Le haché est l'unique porte d'entrée: il est recherché à chaque
        // ouverture de lien, et deux jetons ne peuvent pas coïncider.
        $this->forge->addUniqueKey("token_hash");
        $this->forge->addKey("user_id");
        $this->forge->createTable("password_resets");

        $this->db->query(
            "ALTER TABLE `password_resets` ADD CONSTRAINT `password_resets_user_foreign`"
            . " FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)"
            . " ON DELETE CASCADE ON UPDATE CASCADE"
        );
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `password_resets` DROP FOREIGN KEY `password_resets_user_foreign`");
        $this->forge->dropTable("password_resets");
    }
}
