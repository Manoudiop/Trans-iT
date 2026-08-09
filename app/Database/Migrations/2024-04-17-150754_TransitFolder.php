<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TransitFolder extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'unique' => true
            ],
            'open_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'handling_agent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'repository' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'orbus_number' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'expeditor' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bl' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'unique' => true
            ],
            'bl_of' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'boat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'boat_of' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'manifest' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'article' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'declaration' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'article' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'recipient' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'recipient_address' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'invoice_to' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transit_order' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'transit_order_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'invoice' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'invoice_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'receipt' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'receipt_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'check' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'check_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'customs_admission_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'customs_inspector' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'bae_date' => [
                'type' =>  'DATE',
                'null' => true,
            ],
            'delivery_date' => [
                'type' =>  'DATE',
                'null' => true,
            ],
            'reserve' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'missing' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->addForeignKey("invoice_to", "clients", "id", "CASCADE", "SET NULL");
        $this->forge->createTable("transit_folders");
    }

    public function down()
    {
        $this->forge->dropTable("transit_folders");
    }
}
