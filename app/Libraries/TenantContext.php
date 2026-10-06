<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Tenant (agence) courant de la requête.
 *
 * Phase 1: la résolution se fait par la session, renseignée à la connexion.
 * resolve() est le seul endroit à modifier pour passer à une résolution par
 * sous-domaine (agence.trans-it.sn) — les modèles n'ont pas à le savoir.
 */
class TenantContext
{
    private ?int $id = null;
    private bool $resolved = false;

    /** Fixe explicitement le tenant (connexion, ou commande CLI ciblée). */
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
     * Identifiant du tenant, ou exception.
     *
     * Le cloisonnement doit échouer bruyamment: une requête cloisonnée sans
     * tenant signifie un défaut de conception, pas un cas à traiter
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

    private function resolve(): ?int
    {
        // En CLI il n'y a pas de session. Les commandes qui doivent traverser
        // les agences passent par le query builder brut; celles qui ciblent
        // une agence appellent set().
        if (is_cli()) {
            return null;
        }

        $id = session()->get("tenantId");

        return $id ? (int) $id : null;
    }
}
