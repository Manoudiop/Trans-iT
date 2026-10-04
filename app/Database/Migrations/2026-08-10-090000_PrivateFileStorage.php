<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PrivateFileStorage extends Migration
{
    public function up()
    {
        // Les pièces jointes quittent public/ pour writable/uploads: on stocke
        // désormais un chemin relatif au stockage privé, plus une URL publique.
        // La colonne "url" est conservée le temps de migrer les fichiers déjà
        // en place (voir la commande spark files:migrate).
        $this->forge->addColumn("transit_files", [
            "path" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => true,
                "after" => "url",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("transit_files", "path");
    }
}
