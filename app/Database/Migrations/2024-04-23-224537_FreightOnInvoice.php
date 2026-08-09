<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FreightOnInvoice extends Migration
{
    public function up()
    {
        $this->forge->addColumn("transit_folders",[
            'freight' => [
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
