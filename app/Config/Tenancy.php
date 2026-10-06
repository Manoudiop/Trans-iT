<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Résolution de l'agence par sous-domaine.
 *
 * Phase 2: chaque agence entre par <slug>.<baseDomain>. Le sous-domaine
 * identifie l'agence AVANT la connexion, ce qui permet de rendre les emails
 * uniques par agence au lieu de l'être globalement.
 */
class Tenancy extends BaseConfig
{
    /**
     * Domaine racine du service. En développement, "localhost" suffit:
     * les navigateurs résolvent *.localhost vers 127.0.0.1.
     *
     * Surchargeable par tenancy.baseDomain dans .env.
     */
    public string $baseDomain = "localhost";

    /**
     * Sous-domaines qui ne désignent jamais une agence.
     */
    public array $reservedSlugs = ["www", "app", "admin", "api", "static", "mail"];

    /**
     * Autorise le repli sur la session quand l'hôte ne porte pas de
     * sous-domaine d'agence.
     *
     * Indispensable en développement, où l'on travaille sur un hôte unique.
     * À passer à false en production pour imposer l'entrée par sous-domaine:
     * l'email n'étant plus unique que par agence, une connexion sur le
     * domaine racine devient ambiguë dès que deux agences partagent un email.
     */
    public bool $allowSessionFallback = true;

    /**
     * Extrait le slug d'agence d'un nom d'hôte, ou null si l'hôte ne
     * correspond pas au motif <slug>.<baseDomain>.
     *
     * Utilisé aussi par Config\App, qui doit décider très tôt — avant la
     * construction de la requête — si l'hôte fait partie des nôtres.
     */
    public function slugFromHost(?string $host): ?string
    {
        if ($host === null or $host === "") {
            return null;
        }

        // Le port ne fait pas partie du nom d'hôte.
        $host = strtolower(explode(":", $host)[0]);
        $suffix = "." . strtolower($this->baseDomain);

        if (!str_ends_with($host, $suffix)) {
            return null;
        }

        $slug = substr($host, 0, -strlen($suffix));

        // Un seul niveau de sous-domaine, et rien de réservé.
        if ($slug === "" or str_contains($slug, ".") or in_array($slug, $this->reservedSlugs, true)) {
            return null;
        }

        return preg_match("/^[a-z0-9][a-z0-9-]*$/", $slug) === 1 ? $slug : null;
    }
}
