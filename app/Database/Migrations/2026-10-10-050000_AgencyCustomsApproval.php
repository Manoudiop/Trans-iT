<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Numéro d'agrément en douane de l'agence.
 *
 * L'agrément est délivré au commissionnaire, pas au dossier: c'est un
 * numéro par maison, qui ne change pas d'un envoi à l'autre. Il était
 * pourtant ressaisi sur chaque note de détail.
 *
 * La colonne homonyme de transit_folders est conservée: elle devient une
 * exception, pour l'envoi dédouané sous l'agrément d'un confrère. Vide,
 * elle laisse l'agrément de l'agence s'imprimer.
 */
class AgencyCustomsApproval extends Migration
{
    public function up()
    {
        $this->forge->addColumn("tenants", [
            "agreement_number" => [
                "type" => "VARCHAR",
                "constraint" => 100,
                "null" => true,
                "after" => "ninea",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("tenants", "agreement_number");
    }
}
