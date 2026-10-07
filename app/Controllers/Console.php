<?php

namespace App\Controllers;

use App\Models\Plans;
use App\Models\Tenants;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Console d'exploitation de la plateforme.
 *
 * Seul endroit de l'application qui travaille volontairement au-dessus du
 * cloisonnement: il faut bien voir toutes les agences pour les administrer.
 * C'est pourquoi les statistiques passent par le query builder brut plutôt
 * que par les modèles cloisonnés, qui renverraient l'agence de l'exploitant.
 *
 * L'accès est filtré par PlatformAdmin, qui relit le drapeau en base.
 */
class Console extends BaseController
{
    public function index()
    {
        $tenants = (new Tenants())->orderBy("name", "asc")->findAll();

        // Trois requêtes agrégées au total, pas une par agence: la console
        // doit rester lisible quand le nombre de clients grandit.
        $users = $this->groupCount("users");
        $folders = $this->groupCount("transit_folders");
        $storage = $this->groupSum("transit_files", "size");

        foreach ($tenants as &$tenant) {
            $id = (int) $tenant["id"];
            $tenant["users_count"] = $users[$id] ?? 0;
            $tenant["folders_count"] = $folders[$id] ?? 0;
            $tenant["storage_bytes"] = $storage[$id] ?? 0;
        }
        unset($tenant);

        return view("console/index", [
            "tenants" => $tenants,
            "plans" => (new Plans())->orderBy("price_cfa", "asc")->findAll(),
        ]);
    }

    /** @return array<int, int> */
    private function groupCount(string $table): array
    {
        $rows = db_connect()->table($table)
            ->select("tenant_id, COUNT(*) AS total")
            ->groupBy("tenant_id")
            ->get()
            ->getResultArray();

        return array_column($rows, "total", "tenant_id");
    }

    /** @return array<int, int> */
    private function groupSum(string $table, string $column): array
    {
        $rows = db_connect()->table($table)
            ->select("tenant_id, SUM(" . $column . ") AS total")
            ->groupBy("tenant_id")
            ->get()
            ->getResultArray();

        return array_column($rows, "total", "tenant_id");
    }

    public function toggle()
    {
        $model = new Tenants();
        $id = (int) ($this->request->getPost("id") ?? 0);
        $tenant = $model->find($id);

        if (!$tenant) {
            throw new PageNotFoundException("Agence introuvable.");
        }

        // Se suspendre soi-même fermerait la console sur-le-champ.
        if ((int) session()->userData["tenant_id"] === $id) {
            return redirect()
                ->back()
                ->with("error", "Vous ne pouvez pas suspendre votre propre agence.");
        }

        try {
            $model->update($id, ["active" => $tenant["active"] ? 0 : 1]);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()
            ->back()
            ->with("message", $tenant["active"]
                ? "Agence « " . $tenant["name"] . " » suspendue."
                : "Agence « " . $tenant["name"] . " » réactivée.");
    }

    public function changePlan()
    {
        $model = new Tenants();
        $id = (int) ($this->request->getPost("id") ?? 0);
        $planId = (int) ($this->request->getPost("plan_id") ?? 0);

        if (!$model->find($id)) {
            throw new PageNotFoundException("Agence introuvable.");
        }

        if (!(new Plans())->find($planId)) {
            return redirect()->back()->with("error", "Offre inconnue.");
        }

        try {
            $model->update($id, ["plan_id" => $planId]);
        } catch (\Throwable $th) {
            return redirect()->back()->with("error", $th->getMessage());
        }

        return redirect()->back()->with("message", "Offre modifiée.");
    }
}
