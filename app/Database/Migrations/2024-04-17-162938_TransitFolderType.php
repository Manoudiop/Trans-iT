<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransitFolderType extends Migration
{
    public function up()
    {
        $this->forge->addColumn("transit_folders", [
            'type' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("transit_folders", "type");
    }
}
