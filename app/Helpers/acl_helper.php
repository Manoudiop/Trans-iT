<?php

if (!function_exists("profil")) {
    /** Profil du compte connecté, ou chaîne vide hors session. */
    function profil(): string
    {
        return (string) (session()->userData["profile"] ?? "");
    }
}

if (!function_exists("peut_facturer")) {
    /**
     * Droit d'accès aux écrans d'argent: factures, encaissements, clients,
     * trésorerie.
     *
     * La règle vivait en double, dans le filtre et dans la mise en page, et
     * les deux avaient divergé: le menu s'affichait dès que le profil
     * n'était pas OPERATEUR, alors que le filtre n'acceptait qu'ADMIN et
     * COMPTABLE. Dix comptes voyaient ainsi des entrées qui leur
     * répondaient 404. Les deux lisent désormais cette fonction.
     */
    function peut_facturer(): bool
    {
        return in_array(profil(), ["ADMIN", "COMPTABLE"], true);
    }
}

if (!function_exists("est_admin")) {
    function est_admin(): bool
    {
        return profil() === "ADMIN";
    }
}
