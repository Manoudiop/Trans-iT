<?php

namespace App\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Tenancy;

/**
 * Valide l'agence désignée par le sous-domaine, avant toute autre logique.
 *
 * Trois cas à fermer:
 *  - sous-domaine inconnu: 404, sans révéler si l'agence existe ailleurs;
 *  - agence suspendue: message explicite, pas un 404 trompeur;
 *  - session ouverte pour une agence, requête arrivant sur le sous-domaine
 *    d'une autre: la session est fermée. Sans ce garde-fou, le sous-domaine
 *    primant sur la session, un utilisateur authentifié chez l'agence A
 *    lirait les données de l'agence B.
 */
class TenantResolution implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $tenant = tenant()->tenantFromHost();

        if ($tenant === null) {
            // L'hôte ne désigne aucune agence: soit un sous-domaine inconnu,
            // soit le domaine racine.
            if (tenant()->hostSlug() !== null) {
                throw PageNotFoundException::forPageNotFound("Agence inconnue.");
            }

            if (!config(Tenancy::class)->allowSessionFallback) {
                throw PageNotFoundException::forPageNotFound(
                    "Accès par le domaine racine désactivé: utilisez le sous-domaine de votre agence."
                );
            }

            return;
        }

        if (!$tenant["active"]) {
            $this->closeSession();

            return redirect()
                ->to("/")
                ->with("error", "Cette agence est suspendue. Contactez l'administrateur.");
        }

        $session = session()->get("tenantId");

        if ($session !== null and (int) $session !== (int) $tenant["id"]) {
            $this->closeSession();

            return redirect()
                ->to("/")
                ->with("error_session", true);
        }
    }

    /**
     * Ferme la session de manière effective.
     *
     * Session::destroy() n'est qu'un session_destroy(): il vide le stockage
     * mais laisse $_SESSION peuplé en mémoire. Le flashdata écrit juste après
     * par redirect()->with() réenregistre alors la session sous le même
     * identifiant, utilisateur authentifié compris. Vider les clés d'abord
     * rend la fermeture indépendante de ce qui écrit ensuite.
     */
    private function closeSession(): void
    {
        // Volontairement sans regenerate() ni destroy(): Session::destroy()
        // n'est qu'un session_destroy(), qui vide le stockage mais laisse
        // $_SESSION peuplé — le flashdata écrit ensuite par
        // redirect()->with() réenregistrerait l'utilisateur authentifié. Et
        // session_regenerate_id(true) a été observé ne pas supprimer
        // l'ancien fichier de session sous Windows, laissant l'ancien jeton
        // valide et authentifié.
        //
        // Retirer les clés sans changer d'identifiant fait réécrire la
        // session en place: le jeton existant survit mais ne porte plus
        // aucune authentification, ce que le filtre Auth refuse.
        session()->remove(["userData", "tenantId"]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
