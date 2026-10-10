<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Nature des postes de facturation.
 *
 * Le découpage reproduit celui de la facture imprimée, qui fait foi: c'est
 * ce que le client reçoit sur papier, et une application qui classerait
 * autrement produirait des chiffres impossibles à rapprocher de ses propres
 * factures.
 *
 * Trois sections, reprises de app/Views/invoices/print.php:
 *
 *  - DÉBOURS: décaissés pour le compte du client et refacturés à
 *    l'identique. C'est la trésorerie sortie, à récupérer.
 *  - INTERVENTIONS: camionnage et groupage, prestations facturées à part.
 *  - RÉMUNÉRATION: commissions, honoraires et taxes sur prestations. C'est
 *    le produit de la maison.
 *
 * H.A.D Ad Valorem et T.P.S figurent en rémunération parce que la facture
 * les y place, aux côtés des commissions et honoraires.
 */
class Invoicing extends BaseConfig
{
    /** Refacturés à l'identique au client. */
    public array $debours = [
        "duties_taxes",
        "agios",
        "freight",
        "bl_stamp",
        "shipping_taxe",
        "boarding_disembarkation",
        "storing_guarding",
        "container_transportation",
        "handling",
        "insurance",
        "transportation",
        "expert_report",
        "customs_excort",
        "demurrage",
        "customs_clearance",
        "postal_package_withdrawal_fees",
        "customs_ts_visit",
        "full_land_rental",
        "visit_admissibility",
        "indirect_fees",
        "orbus_fees",
    ];

    /** Prestations facturées à part sur la facture. */
    public array $interventions = [
        "trucking",
        "grouping",
    ];

    /** Produit propre de la maison. */
    public array $remuneration = [
        "commission_on_disbursements",
        "folder_opening_fees",
        "transit_commission",
        "customs_honorary_fees",
        "had",
        "internal_handling",
        "loading_unloading",
        "printer",
        "procedures_formalities",
        "tps",
    ];

    /** Tous les postes de la facture. */
    public function postes(): array
    {
        return array_merge($this->debours, $this->interventions, $this->remuneration);
    }

    /**
     * Postes présents en base mais classés nulle part.
     *
     * Un poste oublié disparaîtrait silencieusement des totaux: la page
     * d'encours le signale plutôt que de laisser l'écart passer.
     *
     * @param  list<string> $colonnes Colonnes de frais de transit_folders
     * @return list<string>
     */
    public function unclassified(array $colonnes): array
    {
        return array_values(array_diff($colonnes, $this->postes()));
    }

    public function deboursSql(): string
    {
        return $this->sumOf($this->debours);
    }

    public function interventionsSql(): string
    {
        return $this->sumOf($this->interventions);
    }

    public function remunerationSql(): string
    {
        return $this->sumOf($this->remuneration);
    }

    /**
     * Expression SQL sommant une liste de postes.
     *
     * Les noms viennent de cette configuration, jamais d'une requête: ce sont
     * des constantes du code. Le filtre garantit qu'une faute de frappe ne
     * fabrique pas d'identifiant inattendu.
     *
     * @param list<string> $postes
     */
    private function sumOf(array $postes): string
    {
        $colonnes = array_filter(
            $postes,
            static fn (string $poste): bool => preg_match("/^[a-z_]+$/", $poste) === 1
        );

        if ($colonnes === []) {
            return "0";
        }

        return implode(" + ", array_map(
            static fn (string $poste): string => "COALESCE(`" . $poste . "`, 0)",
            $colonnes
        ));
    }

    /** Tranches d'antériorité de l'encours, en jours. */
    public array $aging = [
        "b0_30" => ["label" => "0 à 30 j", "from" => 0, "to" => 30],
        "b31_60" => ["label" => "31 à 60 j", "from" => 31, "to" => 60],
        "b61_90" => ["label" => "61 à 90 j", "from" => 61, "to" => 90],
        "b90_plus" => ["label" => "plus de 90 j", "from" => 91, "to" => null],
    ];
}
