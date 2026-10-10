<?php

namespace App\Models;

/**
 * Comptes de trésorerie: caisse physique et comptes bancaires.
 */
class CashAccounts extends TenantModel
{
    protected $table            = 'cash_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ["name", "type", "opening_balance", "active"];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        "name" => "required|max_length[255]",
        "type" => "required|in_list[caisse,banque]",
    ];

    /**
     * Comptes actifs avec leur solde courant.
     *
     * Le solde est calculé, jamais stocké: il se déduit du solde d'ouverture
     * et du journal. Un total dénormalisé finirait par diverger du journal,
     * et c'est le journal qui fait foi.
     *
     * @return list<array<string, mixed>>
     */
    public function withBalances(): array
    {
        $comptes = $this->where("active", 1)->orderBy("type", "asc")->orderBy("name", "asc")->findAll();

        if ($comptes === []) {
            return [];
        }

        $mouvements = (new CashMovements())->balancesByAccount();

        foreach ($comptes as &$compte) {
            $compte["balance"] = (float) $compte["opening_balance"]
                + (float) ($mouvements[$compte["id"]] ?? 0);
        }
        unset($compte);

        return $comptes;
    }
}
