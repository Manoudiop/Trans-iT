<?php

namespace App\Models;

use CodeIgniter\Model;

class TransitFolders extends Model
{
    protected $table            = 'transit_folders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'open_date',
        'handling_agent',
        'repository',
        'orbus_number',
        'expeditor',
        'bl',
        'bl_of',
        'boat',
        'boat_of',
        'manifest',
        'article',
        'declaration',
        'article',
        'recipient',
        'recipient_address',
        'invoice_to',
        'transit_order',
        'transit_order_date',
        'invoice',
        'invoice_date',
        'receipt',
        'receipt_date',
        'check',
        'check_date',
        'customs_admission_date',
        'customs_inspector',
        'bae_date',
        'delivery_date',
        'reserve',
        'missing',
        'type',
        "closed",
        "invoiced",
        "reference",
        "invoice_author",
        "designation",
        "duties_taxes",
        "agios",
        "bl_stamp",
        "shipping_taxe",
        "boarding_disembarkation",
        "storing_guarding",
        "container_transportation",
        "handling",
        "insurance",
        "transportation",
        "expert_report",
        "customs_excort",
        "demurrage",
        "customs_clearance",
        "postal_package_withdrawal_fees",
        "customs_ts_visit",
        "full_land_rental",
        "visit_admissibility",
        "indirect_fees",
        "orbus_fees",
        "trucking",
        "grouping",
        "commission_on_disbursements",
        "folder_opening_fees",
        "transit_commission",
        "customs_honorary_fees",
        "had",
        "internal_handling",
        "loading_unloading",
        "printer",
        "procedures_formalities",
        "tps",
        "freight",
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
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
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = ["getFolderItems"];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function getFolderItems($folder)
    {
        $items = new TransitFolderItems();
        $clients = new Clients();
        $users = new Users();
        $files = new TransitFiles();
        if (!empty($folder["data"])) {
            if ($folder["method"] == "first" or $folder["id"]) {
                $folder["data"]["items"] = $items->where('folder_id', $folder["data"]["id"])->find();
                $folder["data"]["invoice_to"] = $this->relatedOrPlaceholder($clients, $folder["data"]["invoice_to"]);
                $folder["data"]["invoice_author"] = $this->relatedOrPlaceholder($users, $folder["data"]["invoice_author"]);
                $folder["data"]["files"] = $files->where("folder_id", $folder["data"]["id"])->find();

                $folder["data"]["invoice_amount"] =
                    $folder["data"]["duties_taxes"] +
                    $folder["data"]["agios"] +
                    $folder["data"]["bl_stamp"] +
                    $folder["data"]["shipping_taxe"] +
                    $folder["data"]["boarding_disembarkation"] +
                    $folder["data"]["storing_guarding"] +
                    $folder["data"]["container_transportation"] +
                    $folder["data"]["handling"] +
                    $folder["data"]["insurance"] +
                    $folder["data"]["transportation"] +
                    $folder["data"]["expert_report"] +
                    $folder["data"]["customs_excort"] +
                    $folder["data"]["demurrage"] +
                    $folder["data"]["customs_clearance"] +
                    $folder["data"]["postal_package_withdrawal_fees"] +
                    $folder["data"]["customs_ts_visit"] +
                    $folder["data"]["full_land_rental"] +
                    $folder["data"]["visit_admissibility"] +
                    $folder["data"]["indirect_fees"] +
                    $folder["data"]["orbus_fees"] +
                    $folder["data"]["trucking"] +
                    $folder["data"]["grouping"] +
                    $folder["data"]["commission_on_disbursements"] +
                    $folder["data"]["folder_opening_fees"] +
                    $folder["data"]["transit_commission"] +
                    $folder["data"]["customs_honorary_fees"] +
                    $folder["data"]["had"] +
                    $folder["data"]["internal_handling"] +
                    $folder["data"]["loading_unloading"] +
                    $folder["data"]["printer"] +
                    $folder["data"]["procedures_formalities"] +
                    $folder["data"]["tps"] +
                    $folder["data"]["freight"];

                $folder["data"]["items_count"] = 0;
                $folder["data"]["items_count"] = 0;
                $folder["data"]["total_weight"] = 0;
                foreach ($folder["data"]["items"] as $item) {
                    $folder["data"]["items_count"] += $item["quantity"];
                    $folder["data"]["total_weight"] += $item["weight"];
                }
            } else {
                for ($i = 0; $i < count($folder["data"]); $i++) {
                    $folder["data"][$i]["items"] = $items->where('folder_id', $folder['data'][$i]["id"])->find();
                    $folder["data"][$i]["invoice_to"] = $this->relatedOrPlaceholder($clients, $folder["data"][$i]["invoice_to"]);
                    $folder["data"][$i]["invoice_author"] = $this->relatedOrPlaceholder($users, $folder["data"][$i]["invoice_author"]);
                    $folder["data"][$i]["files"] = $files->where("folder_id", $folder["data"][$i]["id"])->find();
                    $folder["data"][$i]["invoice_amount"] =
                        $folder["data"][$i]["duties_taxes"] +
                        $folder["data"][$i]["agios"] +
                        $folder["data"][$i]["bl_stamp"] +
                        $folder["data"][$i]["shipping_taxe"] +
                        $folder["data"][$i]["boarding_disembarkation"] +
                        $folder["data"][$i]["storing_guarding"] +
                        $folder["data"][$i]["container_transportation"] +
                        $folder["data"][$i]["handling"] +
                        $folder["data"][$i]["insurance"] +
                        $folder["data"][$i]["transportation"] +
                        $folder["data"][$i]["expert_report"] +
                        $folder["data"][$i]["customs_excort"] +
                        $folder["data"][$i]["demurrage"] +
                        $folder["data"][$i]["customs_clearance"] +
                        $folder["data"][$i]["postal_package_withdrawal_fees"] +
                        $folder["data"][$i]["customs_ts_visit"] +
                        $folder["data"][$i]["full_land_rental"] +
                        $folder["data"][$i]["visit_admissibility"] +
                        $folder["data"][$i]["indirect_fees"] +
                        $folder["data"][$i]["orbus_fees"] +
                        $folder["data"][$i]["trucking"] +
                        $folder["data"][$i]["grouping"] +
                        $folder["data"][$i]["commission_on_disbursements"] +
                        $folder["data"][$i]["folder_opening_fees"] +
                        $folder["data"][$i]["transit_commission"] +
                        $folder["data"][$i]["customs_honorary_fees"] +
                        $folder["data"][$i]["had"] +
                        $folder["data"][$i]["internal_handling"] +
                        $folder["data"][$i]["loading_unloading"] +
                        $folder["data"][$i]["printer"] +
                        $folder["data"][$i]["procedures_formalities"] +
                        $folder["data"][$i]["tps"] +
                        $folder["data"][$i]["freight"];
                    $folder["data"][$i]["items_count"] = 0;
                    $folder["data"][$i]["items_count"] = 0;
                    $folder["data"][$i]["total_weight"] = 0;
                    foreach ($folder["data"][$i]["items"] as $item) {
                        $folder["data"][$i]["items_count"] += $item["quantity"];
                        $folder["data"][$i]["total_weight"] += $item["weight"];
                    }
                }
            }
        }
        return $folder;
    }

    /**
     * Résout un enregistrement lié par son identifiant.
     *
     * find(null) renvoie toute la table dans CodeIgniter: sans cette garde, un
     * dossier sans client ou sans auteur de facturation reçoit la liste
     * complète, et les vues qui lisent ["id"] ou ["name"] lèvent une
     * ErrorException. Le repli conserve la forme attendue par les vues.
     */
    private function relatedOrPlaceholder(Model $model, $id): array
    {
        $record = $id ? $model->find($id) : null;

        return $record ?: [
            "id" => null,
            "name" => "INFORMATIONS INDISPONIBLES",
            "email" => null,
        ];
    }
}
