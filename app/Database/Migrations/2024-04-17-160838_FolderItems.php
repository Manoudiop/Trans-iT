<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FolderItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'folder_id' => [
                'type' => 'BIGINT',
                'constraint' => 20,
                'unsigned' => true,
                'null' => false,
            ],
            'brand' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'quantity' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
            ],
            'nature' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'weight' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'volume' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->addForeignKey("folder_id","transit_folders","id","CASCADE","CASCADE");
        $this->forge->createTable("folder_items");
    }

    public function down()
    {
        $this->forge->dropTable("folder_items");
    }
}
