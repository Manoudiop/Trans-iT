<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use FontLib\Table\Type\name;

class CorrectCustomsTsVisitColumn extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn("transit_folders", [
            'cutoms_ts_visit' => [
                'name' => 'customs_ts_visit',
                'type' => 'DOUBLE',
                'default' => 0,
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
