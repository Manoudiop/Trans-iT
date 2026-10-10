<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Étapes d'exploitation d'un dossier et seuils d'alerte.
 *
 * L'étape n'est pas stockée: elle se déduit des dates déjà saisies, qui
 * racontent le parcours réel du dossier. Rien à ressaisir, rien à maintenir
 * en double, et les dossiers existants sont classés sans reprise de données.
 *
 * Les seuils diffèrent d'une étape à l'autre, et c'est tout l'intérêt par
 * rapport à une règle unique sur l'âge du dossier: attendre cinq jours un
 * BAE est courant, laisser passer deux jours après le BAE fait courir les
 * surestaries.
 */
class Workflow extends BaseConfig
{
    /**
     * Étapes, dans l'ordre du parcours.
     *
     * - label     : intitulé affiché
     * - threshold : jours passés dans l'étape au-delà desquels on alerte
     * - action    : ce qu'il y a à faire pour débloquer
     */
    public array $stages = [
        "ouvert" => [
            "label" => "Ouvert, en attente d'ordre",
            "threshold" => 5,
            "action" => "Relancer le client pour l'ordre de transit et les documents.",
        ],
        "a_declarer" => [
            "label" => "À déclarer",
            "threshold" => 3,
            "action" => "Déposer la déclaration en douane.",
        ],
        "en_douane" => [
            "label" => "En douane, BAE non obtenu",
            "threshold" => 5,
            "action" => "Suivre la liquidation, la visite et l'inspecteur.",
        ],
        "a_livrer" => [
            "label" => "BAE obtenu, à enlever",
            "threshold" => 2,
            "action" => "Enlever et livrer sans délai: les surestaries courent.",
        ],
        "a_facturer" => [
            "label" => "Livré, à facturer",
            "threshold" => 7,
            "action" => "Établir la facture et récupérer les justificatifs de débours.",
        ],
        "a_encaisser" => [
            "label" => "Facturé, à encaisser",
            "threshold" => 30,
            "action" => "Relancer le règlement: la trésorerie avancée n'est pas rentrée.",
        ],
    ];

    public function label(string $stage): string
    {
        return $this->stages[$stage]["label"] ?? $stage;
    }

    public function threshold(string $stage): int
    {
        return (int) ($this->stages[$stage]["threshold"] ?? 0);
    }

    public function action(string $stage): string
    {
        return $this->stages[$stage]["action"] ?? "";
    }

    /** Un dossier est bloqué quand il dépasse le seuil de son étape. */
    public function isBlocked(string $stage, ?int $days): bool
    {
        return $days !== null and $days > $this->threshold($stage);
    }
}
