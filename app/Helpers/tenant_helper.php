<?php

use App\Libraries\Quotas;
use App\Libraries\TenantContext;

if (!function_exists("tenant")) {
    /** Contexte de l'agence courante. */
    function tenant(): TenantContext
    {
        return service("tenantContext");
    }
}

if (!function_exists("tenant_id")) {
    /** Identifiant de l'agence courante, ou exception si absent. */
    function tenant_id(): int
    {
        return tenant()->idOrFail();
    }
}

if (!function_exists("agency")) {
    /**
     * Fiche de l'agence courante, ou null hors contexte (page de connexion).
     *
     * Mémorisée: la mise en page l'interroge à chaque requête pour le logo,
     * et une lecture par vue en ferait une requête de plus sur chaque page.
     */
    function agency(): ?array
    {
        static $agence = false;

        if ($agence === false) {
            $id = tenant()->id();
            $agence = $id === null ? null : (new App\Models\Tenants())->find($id);
        }

        return $agence;
    }
}

if (!function_exists("agency_logo_url")) {
    /** URL du logo de l'agence, ou null si elle n'en a pas déposé. */
    function agency_logo_url(): ?string
    {
        return empty(agency()["logo_path"]) ? null : base_url("logo-agence");
    }
}

if (!function_exists("quotas")) {
    /** Quotas de l'agence courante, lus depuis son offre. */
    function quotas(): Quotas
    {
        return service("quotas");
    }
}
