<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Caisse, banque et mouvements de trésorerie.
 *
 * Le logiciel savait ce qu'il facturait et ce que les clients payaient. Il
 * ne savait rien de l'argent qui sort: ni les droits réellement versés à la
 * douane, ni les frais de port, ni le loyer, ni ce qu'un agent détient quand
 * il part régler des formalités.
 *
 * Deux tables suffisent: les comptes, et un journal unique.
 *
 * Le journal distingue quatre natures de mouvement, choisies pour coller à
 * une pratique sans formalisme:
 *
 *  - recette: de l'argent entre sur un compte;
 *  - depense: de l'argent est dépensé. Si un agent est renseigné, il dépense
 *    ce qu'il détient déjà et le compte n'est pas touché; sinon le compte
 *    est débité directement;
 *  - avance: le compte est débité et l'agent détient la somme;
 *  - retour: l'agent rend du liquide, le compte est crédité.
 *
 * Ce qu'un agent détient se déduit donc du journal — avances moins dépenses
 * justifiées moins retours — sans bon de caisse ni décharge signée.
 */
class CashBook extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "tenant_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => false],
            "name" => ["type" => "VARCHAR", "constraint" => 255],
            "type" => ["type" => "VARCHAR", "constraint" => 20, "default" => "caisse"],
            "opening_balance" => ["type" => "DOUBLE", "null" => false, "default" => 0],
            "active" => ["type" => "TINYINT", "constraint" => 1, "default" => 1],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
            "deleted_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("cash_accounts");

        $this->forge->addField([
            "id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "auto_increment" => true],
            "tenant_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => false],
            "account_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "kind" => ["type" => "VARCHAR", "constraint" => 20, "null" => false],
            // Toujours positif: le sens vient de kind, pas du signe. Une
            // saisie négative par erreur ne peut donc pas inverser un
            // mouvement.
            "amount" => ["type" => "DOUBLE", "null" => false, "default" => 0],
            "moved_at" => ["type" => "DATE", "null" => true],
            "category" => ["type" => "VARCHAR", "constraint" => 50, "null" => true],
            // Dossier concerné, pour pouvoir comparer le décaissé au facturé.
            "folder_id" => ["type" => "BIGINT", "constraint" => 20, "unsigned" => true, "null" => true],
            // Agent qui détient ou justifie la somme.
            "agent_id" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "reference" => ["type" => "VARCHAR", "constraint" => 255, "null" => true],
            "note" => ["type" => "VARCHAR", "constraint" => 255, "null" => true],
            "recorded_by" => ["type" => "INT", "constraint" => 11, "unsigned" => true, "null" => true],
            "created_at" => ["type" => "DATETIME", "null" => true],
            "updated_at" => ["type" => "DATETIME", "null" => true],
            "deleted_at" => ["type" => "DATETIME", "null" => true],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("cash_movements");

        foreach ($this->indexes() as $sql) {
            $this->db->query($sql);
        }

        // Chaque agence démarre avec une caisse et une banque: sans compte,
        // aucun mouvement n'est saisissable, et obliger à en créer un avant
        // la première saisie est une friction inutile.
        $now = date("Y-m-d H:i:s");
        $lignes = [];

        foreach ($this->db->table("tenants")->select("id")->get()->getResultArray() as $tenant) {
            foreach ([["Caisse", "caisse"], ["Banque", "banque"]] as [$nom, $type]) {
                $lignes[] = [
                    "tenant_id" => $tenant["id"],
                    "name" => $nom,
                    "type" => $type,
                    "opening_balance" => 0,
                    "active" => 1,
                    "created_at" => $now,
                    "updated_at" => $now,
                ];
            }
        }

        if ($lignes !== []) {
            $this->db->table("cash_accounts")->insertBatch($lignes);
        }
    }

    private function indexes(): array
    {
        return [
            "ALTER TABLE `cash_accounts` ADD KEY `cash_accounts_tenant` (`tenant_id`, `active`)",
            "ALTER TABLE `cash_movements` ADD KEY `cash_movements_tenant_date` (`tenant_id`, `moved_at`)",
            "ALTER TABLE `cash_movements` ADD KEY `cash_movements_tenant_account` (`tenant_id`, `account_id`)",
            "ALTER TABLE `cash_movements` ADD KEY `cash_movements_tenant_folder` (`tenant_id`, `folder_id`)",
            "ALTER TABLE `cash_movements` ADD KEY `cash_movements_tenant_agent` (`tenant_id`, `agent_id`)",
            // Un mouvement rattaché à un dossier ne peut pas pointer vers
            // celui d'une autre agence.
            //
            // RESTRICT et non SET NULL: une clé composite dont tenant_id est
            // NOT NULL ne peut pas être mise à NULL partiellement. En
            // pratique la contrainte ne se déclenche jamais, les dossiers
            // étant supprimés logiquement — et refuser d'effacer un dossier
            // qui porte des décaissements est de toute façon souhaitable.
            "ALTER TABLE `cash_movements` ADD CONSTRAINT `cash_movements_tenant_folder_foreign`"
                . " FOREIGN KEY (`tenant_id`, `folder_id`) REFERENCES `transit_folders` (`tenant_id`, `id`)"
                . " ON DELETE RESTRICT ON UPDATE CASCADE",
        ];
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `cash_movements` DROP FOREIGN KEY `cash_movements_tenant_folder_foreign`");
        $this->forge->dropTable("cash_movements");
        $this->forge->dropTable("cash_accounts");
    }
}
