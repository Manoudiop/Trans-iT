<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Volume de démonstration: dossiers de transit et colis pour une agence.
 *
 * Sert à mesurer les performances sur un volume réaliste. Avec deux dossiers
 * en base, une requête N+1 ne se voit pas; avec trois cents, elle saute aux
 * yeux.
 *
 * Insère en direct par le query builder: les modèles sont cloisonnés et
 * exigent un contexte d'agence, absent en CLI.
 */
class TransitFoldersDemo extends Seeder
{
    private const TENANT_ID = 1;
    private const FOLDERS = 300;

    public function run()
    {
        $clients = $this->db->table("clients")
            ->select("id")
            ->where("tenant_id", self::TENANT_ID)
            ->get()
            ->getResultArray();

        $users = $this->db->table("users")
            ->select("id")
            ->where("tenant_id", self::TENANT_ID)
            ->get()
            ->getResultArray();

        if ($clients === [] or $users === []) {
            echo "Agence " . self::TENANT_ID . ": clients ou utilisateurs manquants, rien à faire.\n";
            return;
        }

        $clientIds = array_column($clients, "id");
        $userIds = array_column($users, "id");

        // Les postes de frais d'une facture de transit.
        $fees = [
            "duties_taxes", "agios", "bl_stamp", "shipping_taxe",
            "boarding_disembarkation", "storing_guarding", "container_transportation",
            "handling", "insurance", "transportation", "expert_report",
            "customs_excort", "demurrage", "customs_clearance",
            "postal_package_withdrawal_fees", "customs_ts_visit", "full_land_rental",
            "visit_admissibility", "indirect_fees", "orbus_fees", "trucking",
            "grouping", "commission_on_disbursements", "folder_opening_fees",
            "transit_commission", "customs_honorary_fees", "had",
            "internal_handling", "loading_unloading", "printer",
            "procedures_formalities", "tps", "freight",
        ];

        // Point de départ: au-delà du plus grand numéro déjà présent.
        $last = $this->db->table("transit_folders")
            ->selectMax("id")
            ->where("tenant_id", self::TENANT_ID)
            ->get()
            ->getRowArray();
        $sequence = max((int) substr((string) ($last["id"] ?? 0), -5), 0);

        $year = (int) date("Y");
        $folders = [];
        $items = [];

        // insertBatch construit la liste des colonnes d'après la première
        // ligne: toutes doivent porter exactement les mêmes clés. D'où ce
        // gabarit, fusionné avec les valeurs propres à chaque dossier.
        $template = array_merge(
            array_fill_keys($fees, 0),
            [
                "tenant_id" => self::TENANT_ID,
                "id" => null,
                "open_date" => null,
                "bl" => null,
                "type" => null,
                "invoice_to" => null,
                "expeditor" => null,
                "recipient" => null,
                "closed" => 0,
                "invoiced" => 0,
                // NULL et non chaîne vide: reference est unique par agence,
                // et plusieurs NULL cohabitent là où plusieurs "" entreraient
                // en collision.
                "invoice_author" => null,
                "invoice_date" => null,
                "reference" => null,
                // Jalons du parcours: ce sont eux qui déterminent l'étape
                // affichée par le suivi d'exploitation.
                "transit_order_date" => null,
                "customs_admission_date" => null,
                "bae_date" => null,
                "delivery_date" => null,
                "receipt_date" => null,
            ]
        );

        for ($i = 0; $i < self::FOLDERS; $i++) {
            $month = random_int(1, (int) date("m"));
            $day = random_int(1, 28);
            $sequence++;

            $id = sprintf("%04d%02d%05d", $year, $month, $sequence);
            $ouverture = sprintf("%04d-%02d-%02d", $year, $month, $day);

            // Avancement dans le parcours, de l'ouverture au règlement. Sans
            // cette progression, tous les dossiers resteraient à la première
            // étape et le suivi d'exploitation n'aurait rien à montrer.
            $etape = random_int(0, 6);
            // Jamais au-delà d'aujourd'hui: une date de jalon dans le futur
            // produit des anciennetés négatives dans le suivi et la balance
            // âgée, ce qui ressemble à un défaut de l'application.
            $jalon = static function (int $jours) use ($ouverture): string {
                $date = strtotime($ouverture . " +" . $jours . " days");

                return date("Y-m-d", min($date, time()));
            };

            $invoiced = $etape >= 5;
            $closed = $etape >= 6;

            $folder = array_merge($template, [
                "id" => $id,
                "open_date" => $ouverture,
                "bl" => "BL-" . $year . "-" . str_pad((string) $sequence, 6, "0", STR_PAD_LEFT),
                "type" => random_int(0, 1) ? "IMP" : "EXP",
                "invoice_to" => $clientIds[array_rand($clientIds)],
                "expeditor" => "Expéditeur " . random_int(1, 50),
                "recipient" => "Destinataire " . random_int(1, 50),
                "closed" => $closed ? 1 : 0,
                "invoiced" => $invoiced ? 1 : 0,
            ]);

            if ($etape >= 1) {
                $folder["transit_order_date"] = $jalon(random_int(1, 4));
            }
            if ($etape >= 2) {
                $folder["customs_admission_date"] = $jalon(random_int(4, 9));
            }
            if ($etape >= 3) {
                $folder["bae_date"] = $jalon(random_int(8, 16));
            }
            if ($etape >= 4) {
                $folder["delivery_date"] = $jalon(random_int(10, 20));
            }
            if ($etape >= 6) {
                $folder["receipt_date"] = $jalon(random_int(25, 60));
            }

            if ($invoiced) {
                $folder["invoice_author"] = $userIds[array_rand($userIds)];
                // La facture suit la livraison, elle ne la précède pas.
                $folder["invoice_date"] = $jalon(random_int(14, 24));
                $folder["reference"] = "FAC-" . $year . "-" . str_pad((string) $sequence, 6, "0", STR_PAD_LEFT);
                // Quelques postes seulement sont renseignés sur une facture
                // réelle, pas les trente-trois.
                foreach ((array) array_rand(array_flip($fees), random_int(4, 10)) as $fee) {
                    $folder[$fee] = random_int(5000, 400000);
                }
            }

            $folders[] = $folder;

            foreach (range(1, random_int(1, 5)) as $ignored) {
                $items[] = [
                    "tenant_id" => self::TENANT_ID,
                    "folder_id" => $id,
                    "brand" => "Marque " . random_int(1, 30),
                    "quantity" => random_int(1, 40),
                    "nature" => ["Cartons", "Palettes", "Fûts", "Sacs", "Conteneur"][random_int(0, 4)],
                    "weight" => random_int(50, 25000),
                    "volume" => random_int(1, 60) . " m3",
                ];
            }
        }

        // insertBatch par lots: une seule requête pour trois cents dossiers.
        foreach (array_chunk($folders, 100) as $chunk) {
            $this->db->table("transit_folders")->insertBatch($chunk);
        }
        foreach (array_chunk($items, 200) as $chunk) {
            $this->db->table("folder_items")->insertBatch($chunk);
        }

        echo count($folders) . " dossiers et " . count($items) . " colis créés pour l'agence " . self::TENANT_ID . ".\n";
    }
}
