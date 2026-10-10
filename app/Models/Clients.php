<?php

namespace App\Models;

class Clients extends TenantModel
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["name", "account_number", "email", "phone", "ninea", "ppm"];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = ["generateUniqueId"];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Nombre de clients créés par mois pour une année.
     *
     * Une requête groupée au lieu de douze comptages successifs.
     *
     * @return array<int, int> Indexé de 1 à 12, les mois sans création à 0.
     */
    public function monthlyCounts(int $year): array
    {
        $rows = $this->select("MONTH(created_at) AS mois, COUNT(*) AS total")
            ->where("YEAR(created_at)", $year)
            ->groupBy("MONTH(created_at)")
            ->findAll();

        $parMois = array_fill(1, 12, 0);

        foreach ($rows as $row) {
            $parMois[(int) $row["mois"]] = (int) $row["total"];
        }

        return $parMois;
    }

    protected function generateUniqueId($client)
    {
        $client["data"]["id"] = uniqid("2024");
        //upper case account
        if (isset($client["data"]["account_number"])) {
            $client["data"]["account_number"] = strtoupper($client["data"]["account_number"]);
        }
        return $client;
    }
}
