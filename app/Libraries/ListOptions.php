<?php

namespace App\Libraries;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Tri, taille de page et recherche d'une liste paginée.
 *
 * La colonne de tri arrive par l'URL et finit dans un ORDER BY: elle ne
 * peut donc jamais être prise telle quelle. Seules les colonnes déclarées
 * par l'appelant sont acceptées, et toute autre valeur retombe sur le tri
 * par défaut au lieu de produire une erreur — un lien partagé avec un
 * paramètre périmé doit continuer d'afficher la liste.
 */
class ListOptions
{
    /** Tailles de page proposées. */
    public const TAILLES = [20, 50, 100];

    public readonly string $sort;
    public readonly string $dir;
    public readonly int $perPage;
    public readonly string $search;

    /**
     * Paramètres propres à l'écran — période, mode de règlement — relevés
     * ici pour qu'un clic sur un en-tête ou sur une page ne les perde pas.
     *
     * @var array<string, string>
     */
    public readonly array $extra;

    /**
     * @param array<string, string> $colonnes libellé affiché => colonne SQL
     * @param list<string>          $porter   paramètres GET à conserver
     */
    public function __construct(
        IncomingRequest $request,
        public readonly array $colonnes,
        string $defaut,
        string $sensDefaut = "DESC",
        array $porter = []
    ) {
        $demande = (string) $request->getGet("tri");
        $this->sort = isset($colonnes[$demande]) ? $demande : $defaut;

        $sens = strtoupper((string) $request->getGet("sens"));
        $this->dir = in_array($sens, ["ASC", "DESC"], true)
            ? $sens
            : ($demande === "" ? $sensDefaut : "ASC");

        $taille = (int) $request->getGet("taille");
        $this->perPage = in_array($taille, self::TAILLES, true) ? $taille : self::TAILLES[0];

        $this->search = trim((string) $request->getGet("r"));

        $extra = [];
        foreach ($porter as $nom) {
            $valeur = trim((string) $request->getGet($nom));
            if ($valeur !== "") {
                $extra[$nom] = $valeur;
            }
        }
        $this->extra = $extra;
    }

    /** Colonne SQL correspondant au tri courant. */
    public function column(): string
    {
        return $this->colonnes[$this->sort];
    }

    /**
     * Paramètres de l'URL courante, hors ceux qu'on veut remplacer.
     *
     * @param array<string, string|int|null> $remplace
     * @return array<string, string|int>
     */
    public function query(array $remplace = []): array
    {
        $base = array_merge($this->extra, [
            "tri" => $this->sort,
            "sens" => $this->dir,
            "taille" => $this->perPage,
            "r" => $this->search,
        ]);

        $params = array_merge($base, $remplace);

        return array_filter(
            $params,
            static fn ($valeur): bool => $valeur !== null and $valeur !== ""
        );
    }
}
