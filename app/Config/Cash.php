<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Natures de mouvement et catégories de dépense.
 */
class Cash extends BaseConfig
{
    /**
     * Natures de mouvement.
     *
     * - sign  : effet sur le solde du compte (0 = n'affecte pas le compte)
     * - agent : l'agent est-il requis, interdit, ou facultatif
     */
    public array $kinds = [
        "recette" => [
            "label" => "Recette",
            "sign" => 1,
            "agent" => "interdit",
            "hint" => "Entrée d'argent sur le compte.",
        ],
        "depense" => [
            "label" => "Dépense",
            "sign" => -1,
            "agent" => "facultatif",
            "hint" => "Sortie d'argent. Si un agent est indiqué, il dépense ce qu'il détient déjà et le compte n'est pas débité.",
        ],
        "avance" => [
            "label" => "Avance à un agent",
            "sign" => -1,
            "agent" => "requis",
            "hint" => "Le compte est débité, l'agent détient la somme.",
        ],
        "retour" => [
            "label" => "Retour de liquide",
            "sign" => 1,
            "agent" => "requis",
            "hint" => "L'agent rend du liquide, le compte est crédité.",
        ],
    ];

    /**
     * Catégories de dépense.
     *
     * Celles marquées dossier concernent un dossier précis et sont
     * refacturées au client; les autres sont des charges de la maison.
     */
    public array $categories = [
        "douane" => ["label" => "Droits et taxes de douane", "dossier" => true],
        "port" => ["label" => "Frais de port et terminal", "dossier" => true],
        "magasinage" => ["label" => "Magasinage et gardiennage", "dossier" => true],
        "surestaries" => ["label" => "Surestaries", "dossier" => true],
        "transport" => ["label" => "Transport et camionnage", "dossier" => true],
        "formalites" => ["label" => "Formalités et guichet unique", "dossier" => true],
        "autre_dossier" => ["label" => "Autre frais de dossier", "dossier" => true],
        "salaires" => ["label" => "Salaires et charges", "dossier" => false],
        "loyer" => ["label" => "Loyer et charges locatives", "dossier" => false],
        "carburant" => ["label" => "Carburant et véhicules", "dossier" => false],
        "fournitures" => ["label" => "Fournitures et bureau", "dossier" => false],
        "telecom" => ["label" => "Téléphone et internet", "dossier" => false],
        "impots" => ["label" => "Impôts et taxes de la maison", "dossier" => false],
        "autre_charge" => ["label" => "Autre charge", "dossier" => false],
    ];

    public function label(string $categorie): string
    {
        return $this->categories[$categorie]["label"] ?? $categorie;
    }

    public function isFolderCategory(string $categorie): bool
    {
        return (bool) ($this->categories[$categorie]["dossier"] ?? false);
    }

    /** @return array<string, string> */
    public function folderCategories(): array
    {
        $liste = [];

        foreach ($this->categories as $cle => $categorie) {
            if ($categorie["dossier"]) {
                $liste[$cle] = $categorie["label"];
            }
        }

        return $liste;
    }

    /** @return array<string, string> */
    public function overheadCategories(): array
    {
        $liste = [];

        foreach ($this->categories as $cle => $categorie) {
            if (!$categorie["dossier"]) {
                $liste[$cle] = $categorie["label"];
            }
        }

        return $liste;
    }
}
