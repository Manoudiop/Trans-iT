<?php

if (!function_exists("montant_en_lettres")) {
    /**
     * Montant écrit en toutes lettres, pour la mention portée sur la facture.
     *
     * L'épellation vient d'ICU plutôt que d'une table maison: il gère de
     * lui-même « vingt-et-un », « quatre-vingts » et « deux cents », qui sont
     * précisément les formes qu'une table écrite à la main rate.
     *
     * Le franc CFA n'a pas de subdivision en usage, mais les montants sont
     * stockés en flottant: une part décimale non nulle trahit une saisie et
     * doit se lire sur la facture plutôt que disparaître.
     *
     * @return string Vide si l'extension intl manque, auquel cas la facture
     *                s'imprime simplement sans la mention.
     */
    function montant_en_lettres(
        float $montant,
        string $singulier = "franc CFA",
        string $pluriel = "francs CFA"
    ): string {
        if (!class_exists("NumberFormatter")) {
            return "";
        }

        $negatif = $montant < 0;
        $montant = abs(round($montant, 2));
        $entier = (int) floor($montant);
        $centimes = (int) round(($montant - $entier) * 100);

        $epeleur = new NumberFormatter("fr_FR", NumberFormatter::SPELLOUT);

        // « Un franc », pas « un francs »: le cas est rare sur une facture,
        // mais il saute aux yeux quand il arrive.
        $texte = $epeleur->format($entier) . " " . ($entier < 2 ? $singulier : $pluriel);

        if ($centimes > 0) {
            $texte .= " et " . $epeleur->format($centimes) . " centime" . ($centimes > 1 ? "s" : "");
        }

        if ($negatif) {
            $texte = "moins " . $texte;
        }

        return ucfirst($texte);
    }
}
