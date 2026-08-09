<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CorrectIndirectTaxes extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('transit_folders', [
            'inderect_fees' => [
                'name' => 'indirect_fees',
                'type' => 'DOUBLE',
                'default' => 0
            ],
        ]);
    }

    public function down()
    {
        //
    }
}
