<?php

namespace App\Libraries;

use App\Models\Tenants;
use Config\Tenancy;
use RuntimeException;

/**
 * Agence courante de la requête.
 *
 * Résolution par sous-domaine (<slug>.<baseDomain>), avec repli sur la
 * session quand l'hôte n'en porte pas. Le sous-domaine primant toujours sur
 * la session, une session ouverte pour une agence ne peut pas servir à lire
 * les données d'une autre — le filtre TenantResolution détecte l'écart et
 * ferme la session.
 */
class TenantContext
{
    private ?int $id = null;
    private bool $resolved = false;

    /** Agence résolue depuis l'hôte, mémorisée pour éviter deux requêtes. */
    private ?array $hostTenant = null;
    private bool $hostResolved = false;

    /** Fixe explicitement l'agence (connexion, ou commande CLI ciblée). */
    public function set(?int $id): void
    {
        $this->id = $id;
        $this->resolved = true;
    }

    public function id(): ?int
    {
        if (!$this->resolved) {
            $this->id = $this->resolve();
            $this->resolved = true;
        }

        return $this->id;
    }

    /**
     * Identifiant de l'agence, ou exception.
     *
     * Le cloisonnement doit échouer bruyamment: une requête cloisonnée sans
     * agence signale un défaut de conception, pas un cas à traiter
     * silencieusement en renvoyant toute la table.
     */
    public function idOrFail(): int
    {
        $id = $this->id();

        if ($id === null) {
            throw new RuntimeException(
                "Aucune agence dans le contexte: requête cloisonnée hors session authentifiée."
            );
        }

        return $id;
    }

    /** Slug présent dans l'hôte, indépendamment de son existence en base. */
    public function hostSlug(): ?string
    {
        return $this->config()->slugFromHost($_SERVER["HTTP_HOST"] ?? null);
    }

    /**
     * Agence désignée par l'hôte, ou null si l'hôte n'en désigne aucune.
     *
     * Renvoie la ligne même inactive: c'est au filtre de décider du refus,
     * pour distinguer « agence inconnue » de « agence suspendue ».
     */
    public function tenantFromHost(): ?array
    {
        if ($this->hostResolved) {
            return $this->hostTenant;
        }

        $this->hostResolved = true;
        $slug = $this->hostSlug();

        if ($slug === null) {
            return $this->hostTenant = null;
        }

        $tenant = (new Tenants())->where("slug", $slug)->first();

        return $this->hostTenant = $tenant ?: null;
    }

    private function resolve(): ?int
    {
        // En CLI il n'y a ni hôte ni session. Les commandes qui traversent
        // les agences passent par le query builder brut; celles qui ciblent
        // une agence appellent set().
        if (is_cli()) {
            return null;
        }

        $tenant = $this->tenantFromHost();

        if ($tenant !== null) {
            return $tenant["active"] ? (int) $tenant["id"] : null;
        }

        if (!$this->config()->allowSessionFallback) {
            return null;
        }

        $id = session()->get("tenantId");

        return $id ? (int) $id : null;
    }

    private function config(): Tenancy
    {
        return config(Tenancy::class);
    }
}
