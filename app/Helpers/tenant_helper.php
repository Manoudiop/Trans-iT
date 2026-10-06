<?php

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
