<?php

namespace App\Models;

/**
 * Lignes tarifaires d'une note de détail.
 */
class DeclarationLines extends TenantModel
{
    protected $table            = 'declaration_lines';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "folder_id",
        "line_no",
        "hs_code",
        "description",
        "origin",
        "weight",
        "fob_value",
        "freight_value",
        "insurance_value",
        "caf_value",
        "complementary_quantity",
        "container_chassis",
        "reference",
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        "folder_id" => "required",
        "hs_code" => "required|max_length[20]",
    ];

    protected $validationMessages = [
        "hs_code" => ["required" => "L'espèce tarifaire est obligatoire."],
    ];

    /** Lignes d'un dossier, dans l'ordre du formulaire. */
    public function forFolder($folderId): array
    {
        return $this->where("folder_id", $folderId)
            ->orderBy("line_no", "asc")
            ->orderBy("id", "asc")
            ->findAll();
    }

    /** Prochain numéro de ligne disponible pour un dossier. */
    public function nextLineNo($folderId): int
    {
        $row = $this->selectMax("line_no", "dernier")
            ->where("folder_id", $folderId)
            ->first();

        return (int) ($row["dernier"] ?? 0) + 1;
    }

    /**
     * Totaux de la note, et écarts à signaler.
     *
     * Rien n'est bloquant: le document douanier fait foi. Le logiciel
     * compare et alerte, il ne corrige pas — un déclarant qui arrondit un
     * CAF n'a pas commis d'erreur.
     *
     * @param  list<array<string, mixed>> $lignes
     * @return array<string, mixed>
     */
    public function totals(array $lignes): array
    {
        $poids = 0.0;
        $fob = 0.0;
        $fret = 0.0;
        $assurance = 0.0;
        $caf = 0.0;
        $ecarts = [];

        foreach ($lignes as $ligne) {
            $poids += (float) $ligne["weight"];
            $fob += (float) $ligne["fob_value"];
            $fret += (float) $ligne["freight_value"];
            $assurance += (float) $ligne["insurance_value"];
            $caf += (float) $ligne["caf_value"];

            $calcule = (float) $ligne["fob_value"] + (float) $ligne["freight_value"] + (float) $ligne["insurance_value"];

            // Tolérance d'un franc: les arrondis ne sont pas des erreurs.
            if ($ligne["caf_value"] !== null and abs($calcule - (float) $ligne["caf_value"]) > 1) {
                $ecarts[] = [
                    "ligne" => $ligne["line_no"],
                    "declare" => (float) $ligne["caf_value"],
                    "calcule" => $calcule,
                ];
            }
        }

        return [
            "lignes" => count($lignes),
            "poids" => $poids,
            "fob" => $fob,
            "fret" => $fret,
            "assurance" => $assurance,
            "caf" => $caf,
            "ecarts_caf" => $ecarts,
        ];
    }
}
