<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Offres commerciales.
 *
 * N'étend pas TenantModel: le catalogue est commun à toutes les agences.
 */
class Plans extends Model
{
    protected $table            = 'plans';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "name",
        "slug",
        "max_users",
        "max_folders_per_month",
        "max_storage_mb",
        "price_cfa",
        "active",
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
        "name" => "required|max_length[255]",
        "slug" => "required|max_length[100]|alpha_dash",
    ];

    /** Offre proposée par défaut à l'inscription. */
    public function default(): ?array
    {
        return $this->where("slug", "decouverte")
            ->where("active", 1)
            ->first();
    }
}
