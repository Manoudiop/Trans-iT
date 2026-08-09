<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InvoicesInformations extends Migration
{
    public function up()
    {
        $this->forge->addColumn("transit_folders", [
            'invoiced' => [
                'type' => 'BOOLEAN',
                'default' => false,
            ],
            'reference' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'unique' => true
            ],
            'invoice_author' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'designation' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            "duties_taxes" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "agios" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "bl_stamp" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "shipping_taxe" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "boarding_disembarkation" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "storing_guarding" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "container_transportation" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "handling" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "insurance" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "transportation" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "expert_report" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "customs_excort" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "demurrage" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "customs_clearance" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "postal_package_withdrawal_fees" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "cutoms_ts_visit" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "full_land_rental" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "visit_admissibility" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "inderect_fees" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "orbus_fees" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "trucking" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "grouping" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "commission_on_disbursements" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "folder_opening_fees" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "transit_commission" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "customs_honorary_fees" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "had" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "internal_handling" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "loading_unloading" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "printer" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "procedures_formalities" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
            "tps" => [
                'type' => 'DOUBLE',
                'null' => true,
                'default' => 0
            ],
        ]);
        $this->forge->addForeignKey("invoice_author", "users", "id", "CASCADE", "SET NULL");
    }

    public function down()
    {
        //
    }
}
