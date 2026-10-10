<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Nature des postes de facturation.
 *
 * Une facture de transit mélange deux choses de nature opposée:
 *
 *  - les DÉBOURS, décaissés par la maison pour le compte du client et
 *    refacturés à l'identique — droits et taxes, magasinage, surestaries,
 *    fret. C'est de la trésorerie sortie, à récupérer.
 *  - la RÉMUNÉRATION de la maison — commissions et honoraires. C'est de la
 *    marge, pas une avance.
 *
 * Le suivi des avances ne vaut que si les deux sont séparés: un encours de
 * dix millions dont neuf de débours n'a pas le même sens qu'un encours de
 * dix millions d'honoraires.
 *
 * Tout poste non listé ci-dessous est traité comme un débours. C'est le
 * choix prudent: il vaut mieux surestimer la trésorerie à récupérer que la
 * sous-estimer.
 */
class Invoicing extends BaseConfig
{
    /**
     * Postes constituant la rémunération propre de la maison.
     *
     * À vérifier et compléter selon vos pratiques: la frontière n'est pas la
     * même partout, notamment pour les frais de dédouanement et les
     * formalités, qui sont tantôt refacturés à l'identique, tantôt facturés
     * comme prestation.
     */
    public array $remuneration = [
        "transit_commission",
        "customs_honorary_fees",
        "folder_opening_fees",
        "commission_on_disbursements",
    ];

    /** Tous les postes de la facture, dans l'ordre du formulaire. */
    public array $postes = [
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

    /** Postes refacturés à l'identique. */
    public function debours(): array
    {
        return array_values(array_diff($this->postes, $this->remuneration));
    }

    /**
     * Expression SQL sommant les débours d'une ligne.
     *
     * Les noms viennent de cette configuration, jamais d'une requête: ce sont
     * des constantes du code. Le filtre sur $postes garantit qu'une faute de
     * frappe dans $remuneration ne fabrique pas d'identifiant inattendu.
     */
    public function deboursSql(): string
    {
        $colonnes = array_filter(
            $this->debours(),
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
