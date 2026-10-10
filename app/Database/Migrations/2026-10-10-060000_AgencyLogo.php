<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Logo propre à chaque agence.
 *
 * Seul le chemin relatif est stocké; le fichier vit dans WRITEPATH/uploads,
 * hors de la racine web, comme les pièces jointes des dossiers depuis le
 * passage au stockage privé. Il est servi par une route authentifiée, de
 * sorte qu'aucune agence ne puisse atteindre le logo d'une autre en
 * devinant une URL.
 */
class AgencyLogo extends Migration
{
    public function up()
    {
        $this->forge->addColumn("tenants", [
            "logo_path" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
                "after" => "agreement_number",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("tenants", "logo_path");
    }
}
