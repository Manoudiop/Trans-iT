<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WeightToKilo extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn("folder_items", [
            'weight' => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
