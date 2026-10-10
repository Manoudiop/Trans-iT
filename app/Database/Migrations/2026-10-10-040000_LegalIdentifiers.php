<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Identifiants légaux de l'agence et de ses clients.
 *
 * Une facture sénégalaise porte l'identité fiscale de celui qui l'émet:
 * raison sociale, adresse, téléphone et NINEA. L'en-tête ne portait que le
 * nom de l'agence, le reste était écrit en dur dans la vue.
 *
 * Côté clients, le NINEA et le PPM ne concernent que les entreprises: un
 * particulier n'en a pas. Les deux colonnes sont donc facultatives, et une
 * facture n'affiche que celles qui sont renseignées.
 */
class LegalIdentifiers extends Migration
{
    public function up()
    {
        $this->forge->addColumn("tenants", [
            "address" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
                "after" => "slug",
            ],
            "phone" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
                "after" => "address",
            ],
            "ninea" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
                "after" => "phone",
            ],
        ]);

        $this->forge->addColumn("clients", [
            "ninea" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
                "after" => "phone",
            ],
            "ppm" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true,
                "after" => "ninea",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("tenants", ["address", "phone", "ninea"]);
        $this->forge->dropColumn("clients", ["ninea", "ppm"]);
    }
}
