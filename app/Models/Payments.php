<?php

namespace App\Models;

/**
 * Règlements d'une facture, partiels ou totaux.
 *
 * Le modèle maintient lui-même transit_folders.paid_amount: le total
 * encaissé est recalculé après chaque écriture, par une somme en base. Le
 * laisser à la charge des contrôleurs reviendrait à parier qu'aucun appelant
 * futur ne l'oubliera.
 */
class Payments extends TenantModel
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "folder_id",
        "amount",
        "paid_at",
        "method",
        "reference",
        "recorded_by",
        "note",
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        "folder_id" => "required",
        "amount" => "required|greater_than[0]",
        "paid_at" => "required|valid_date",
    ];

    protected $validationMessages = [
        "amount" => [
            "greater_than" => "Le montant du règlement doit être supérieur à zéro.",
        ],
    ];

    // Callbacks
    protected $allowCallbacks = true;
    protected $afterInsert    = ["recomputeFromEvent"];
    protected $afterUpdate    = ["recomputeFromEvent"];
    protected $beforeDelete   = ["rememberFolders"];
    protected $afterDelete    = ["recomputeRemembered"];

    /** Dossiers dont le total est à recalculer après une suppression. */
    private array $pendingFolders = [];

    /** Méthodes de règlement proposées à la saisie. */
    public const METHODES = [
        "especes" => "Espèces",
        "cheque" => "Chèque",
        "virement" => "Virement",
        "mobile" => "Mobile money",
        "compensation" => "Compensation",
    ];

    /** Règlements d'un dossier, du plus récent au plus ancien. */
    public function forFolder($folderId): array
    {
        return $this->where("folder_id", $folderId)
            ->orderBy("paid_at", "desc")
            ->orderBy("id", "desc")
            ->findAll();
    }

    /**
     * Recalcule le total encaissé d'un dossier.
     *
     * Somme en base plutôt qu'incrément en PHP: le total reste juste même si
     * un règlement est modifié, supprimé, ou si deux écritures se croisent.
     */
    public function recompute($folderId): void
    {
        if (empty($folderId)) {
            return;
        }

        $this->db->query(
            "UPDATE `transit_folders` f SET f.`paid_amount` = COALESCE("
            . "(SELECT SUM(p.`amount`) FROM `payments` p"
            . " WHERE p.`tenant_id` = f.`tenant_id` AND p.`folder_id` = f.`id` AND p.`deleted_at` IS NULL), 0)"
            . " WHERE f.`tenant_id` = ? AND f.`id` = ?",
            [tenant_id(), $folderId]
        );
    }

    protected function recomputeFromEvent(array $event): array
    {
        $folderId = $event["data"]["folder_id"] ?? null;

        // update() ne transporte pas forcément folder_id: on le relit.
        if ($folderId === null and !empty($event["id"])) {
            foreach ((array) $event["id"] as $id) {
                $ligne = $this->withDeleted()->find($id);
                if ($ligne !== null) {
                    $this->recompute($ligne["folder_id"]);
                }
            }

            return $event;
        }

        $this->recompute($folderId);

        return $event;
    }

    /** La ligne disparaît du filtre après suppression: on note son dossier avant. */
    protected function rememberFolders(array $event): array
    {
        $this->pendingFolders = [];

        foreach ((array) ($event["id"] ?? []) as $id) {
            $ligne = $this->withDeleted()->find($id);
            if ($ligne !== null) {
                $this->pendingFolders[] = $ligne["folder_id"];
            }
        }

        return $event;
    }

    protected function recomputeRemembered(array $event): array
    {
        foreach (array_unique($this->pendingFolders) as $folderId) {
            $this->recompute($folderId);
        }

        $this->pendingFolders = [];

        return $event;
    }
}
