<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ClosenFolder extends Migration
{
    public function up()
    {
        $this->forge->addColumn("transit_folders", [
            'closed' => [
                'type' => 'BOOLEAN',
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("transit_folders", "closed");
    }
}
