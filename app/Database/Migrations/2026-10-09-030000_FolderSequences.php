<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Dette 3/3: séquence atomique pour la numérotation des dossiers.
 *
 * generateId() lisait le plus grand numéro existant puis l'incrémentait, ce
 * qui portait trois défauts:
 *
 *  1. Course: deux créations simultanées lisaient le même maximum et
 *     produisaient le même numéro. La clé primaire composite faisait échouer
 *     la seconde bruyamment, mais l'utilisateur voyait une erreur.
 *  2. Bascule d'année: l'année était reprise du dernier numéro, seul le mois
 *     étant remis à jour. En janvier 2027, après un dossier de décembre
 *     2026, le code aurait produit 202601xxxxx — en collision directe avec
 *     les dossiers de janvier 2026.
 *  3. Suppression logique: depuis que les dossiers supprimés sont masqués,
 *     le maximum lu pouvait reculer et entrer en collision avec un numéro
 *     déjà attribué à un dossier en corbeille.
 *
 * Une table de séquences par agence et par mois règle les trois: le numéro
 * est alloué par une écriture atomique, indépendamment de la visibilité des
 * lignes et de ce que contient leur identifiant.
 */
class FolderSequences extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "tenant_id" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => false,
            ],
            "period" => [
                "type" => "CHAR",
                "constraint" => 6,
                "null" => false,
                "comment" => "AAAAMM",
            ],
            "last_number" => [
                "type" => "INT",
                "constraint" => 11,
                "unsigned" => true,
                "null" => false,
                "default" => 0,
            ],
        ]);
        $this->forge->addPrimaryKey(["tenant_id", "period"]);
        $this->forge->createTable("folder_sequences");

        // Amorçage depuis les dossiers existants, supprimés compris: une
        // séquence qui repartirait de zéro réattribuerait des numéros déjà
        // utilisés. SUBSTRING à partir du 7e caractère, les identifiants
        // historiques n'ayant pas tous la même longueur de compteur.
        $this->db->query(
            "INSERT INTO `folder_sequences` (`tenant_id`, `period`, `last_number`)"
            . " SELECT `tenant_id`, LEFT(`id`, 6), MAX(CAST(SUBSTRING(`id`, 7) AS UNSIGNED))"
            . " FROM `transit_folders` GROUP BY `tenant_id`, LEFT(`id`, 6)"
        );
    }

    public function down()
    {
        $this->forge->dropTable("folder_sequences");
    }
}
