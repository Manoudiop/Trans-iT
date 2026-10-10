<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Agences clientes du SaaS.
 *
 * Ce modèle n'étend volontairement pas TenantModel: c'est la table qui porte
 * le cloisonnement, elle ne peut pas être cloisonnée par elle-même.
 */
class Tenants extends Model
{
    protected $table            = 'tenants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ["name", "slug", "active", "plan_id", "address", "phone", "ninea", "agreement_number"];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    /**
     * Le NINEA n'est pas contraint à un format: les agences l'écrivent avec
     * ou sans le code COFI, avec ou sans espaces. Un gabarit trop strict
     * empêcherait de saisir un numéro pourtant valide.
     */
    protected $validationRules = [
        "name" => "required|max_length[255]",
        "slug" => "required|max_length[100]|alpha_dash|is_unique[tenants.slug,id,{id}]",
        "address" => "permit_empty|max_length[255]",
        "phone" => "permit_empty|max_length[50]",
        "ninea" => "permit_empty|max_length[50]",
        "agreement_number" => "permit_empty|max_length[100]",
    ];
}
