<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Reprise des pièces jointes créées avant le passage au stockage privé.
 *
 * Elles vivent à plat dans public/files_uploaded et sont référencées par une
 * URL publique. On les range sous writable/uploads/<agence>/<dossier>/ et on
 * renseigne la colonne "path".
 *
 * La commande passe par le query builder brut et non par le modèle
 * TransitFiles: elle tourne en CLI, sans session, donc sans agence dans le
 * contexte, et doit de toute façon traverser toutes les agences.
 */
class MigrateUploadedFiles extends BaseCommand
{
    protected $group = "Trans-iT";
    protected $name = "files:migrate";
    protected $description = "Déplace les pièces jointes de public/files_uploaded vers le stockage privé.";
    protected $usage = "files:migrate [--dry-run]";
    protected $options = [
        "--dry-run" => "Affiche ce qui serait fait sans rien déplacer.",
    ];

    public function run(array $params)
    {
        $dryRun = (bool) CLI::getOption("dry-run");

        $db = Database::connect();
        $pending = $db->table("transit_files")
            ->where("path", null)
            ->get()
            ->getResultArray();

        if ($pending === []) {
            CLI::write("Aucune pièce jointe à migrer.", "green");
            return;
        }

        CLI::write(count($pending) . " pièce(s) jointe(s) à traiter.", "yellow");

        $moved = 0;
        $missing = 0;

        foreach ($pending as $file) {
            if (empty($file["url"])) {
                CLI::write("  #" . $file["id"] . " : aucune URL, ignoré.", "dark_gray");
                $missing++;
                continue;
            }

            $name = basename($file["url"]);
            $source = ROOTPATH . "public/files_uploaded/" . $name;

            if (!is_file($source)) {
                CLI::error("  #" . $file["id"] . " : fichier absent du disque (" . $name . ").");
                $missing++;
                continue;
            }

            $relative = $file["tenant_id"] . "/" . $file["folder_id"] . "/" . $name;
            $target = WRITEPATH . "uploads/" . $relative;

            if ($dryRun) {
                CLI::write("  #" . $file["id"] . " : " . $name . " -> " . $relative, "dark_gray");
                $moved++;
                continue;
            }

            $directory = dirname($target);
            if (!is_dir($directory) and !mkdir($directory, 0755, true)) {
                CLI::error("  #" . $file["id"] . " : création de " . $directory . " impossible.");
                $missing++;
                continue;
            }

            if (!rename($source, $target)) {
                CLI::error("  #" . $file["id"] . " : déplacement de " . $name . " impossible.");
                $missing++;
                continue;
            }

            $db->table("transit_files")
                ->where("id", $file["id"])
                ->update([
                    "path" => $relative,
                    "updated_at" => date("Y-m-d H:i:s"),
                ]);
            $moved++;
        }

        CLI::newLine();
        CLI::write($moved . " déplacée(s), " . $missing . " en échec.", $missing ? "yellow" : "green");

        if ($dryRun) {
            CLI::write("Simulation: aucune modification enregistrée.", "yellow");
            return;
        }

        if ($missing === 0) {
            CLI::write("public/files_uploaded peut maintenant être supprimé.", "green");
        }
    }
}
