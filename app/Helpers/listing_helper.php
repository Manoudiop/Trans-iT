<?php

use App\Libraries\ListOptions;

if (!function_exists("entete_triable")) {
    /**
     * En-tête de colonne cliquable, qui inverse le sens sur la colonne active.
     *
     * @param string $cle Clé déclarée dans les colonnes triables
     */
    function entete_triable(string $libelle, string $cle, ListOptions $options, string $url): string
    {
        $actif = $options->sort === $cle;
        // Sur la colonne déjà triée on propose l'inverse; sur une autre, on
        // commence par le plus pertinent: décroissant.
        $sens = $actif && $options->dir === "DESC" ? "ASC" : "DESC";

        $lien = $url . "?" . http_build_query($options->query([
            "tri" => $cle,
            "sens" => $sens,
            "page" => null,
        ]));

        $fleche = $actif
            ? ' <i class="ti ti-' . ($options->dir === "DESC" ? "arrow-down" : "arrow-up") . '"></i>'
            : "";

        return '<a class="text-reset text-decoration-none" href="' . esc($lien, "attr") . '">'
            . esc($libelle) . $fleche . "</a>";
    }
}

if (!function_exists("lien_taille_page")) {
    /**
     * Lien changeant le nombre de lignes par page.
     */
    function lien_taille_page(int $taille, ListOptions $options, string $url): string
    {
        return $url . "?" . http_build_query($options->query([
            "taille" => $taille,
            "page" => null,
        ]));
    }
}
