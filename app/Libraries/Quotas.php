<?php

namespace App\Libraries;

use App\Models\Plans;
use App\Models\Tenants;
use App\Models\TransitFiles;
use App\Models\TransitFolders;
use App\Models\Users;

/**
 * Quotas de l'agence courante, lus depuis son offre.
 *
 * Les compteurs passent par les modèles cloisonnés: ils mesurent donc
 * l'agence courante sans qu'aucune condition ne soit écrite ici. Une limite
 * à NULL signifie « illimité » — c'est volontairement distinct de zéro, qui
 * interdirait tout.
 *
 * Une agence sans offre n'est pas bridée: on ne bloque pas l'exploitation
 * d'un client pour un défaut de configuration commerciale.
 */
class Quotas
{
    private ?array $plan = null;
    private bool $planLoaded = false;

    public function plan(): ?array
    {
        if ($this->planLoaded) {
            return $this->plan;
        }

        $this->planLoaded = true;
        $tenant = tenant()->tenantFromHost() ?? $this->currentTenant();

        if ($tenant === null or empty($tenant["plan_id"])) {
            return $this->plan = null;
        }

        return $this->plan = (new Plans())->find($tenant["plan_id"]) ?: null;
    }

    private function currentTenant(): ?array
    {
        $id = tenant()->id();

        if ($id === null) {
            return null;
        }

        return (new Tenants())->find($id) ?: null;
    }

    private function limit(string $key): ?int
    {
        $plan = $this->plan();

        if ($plan === null or $plan[$key] === null) {
            return null;
        }

        return (int) $plan[$key];
    }

    // --- Utilisateurs ---------------------------------------------------

    public function usersUsed(): int
    {
        return (new Users())->countAllResults();
    }

    public function usersLimit(): ?int
    {
        return $this->limit("max_users");
    }

    public function canAddUser(): bool
    {
        $limit = $this->usersLimit();

        return $limit === null or $this->usersUsed() < $limit;
    }

    // --- Dossiers du mois -----------------------------------------------

    public function foldersThisMonth(): int
    {
        return (new TransitFolders())
            ->where("MONTH(open_date)", date("m"))
            ->where("YEAR(open_date)", date("Y"))
            ->countAllResults();
    }

    public function foldersPerMonthLimit(): ?int
    {
        return $this->limit("max_folders_per_month");
    }

    public function canAddFolder(): bool
    {
        $limit = $this->foldersPerMonthLimit();

        return $limit === null or $this->foldersThisMonth() < $limit;
    }

    // --- Stockage --------------------------------------------------------

    public function storageUsedBytes(): int
    {
        // selectSum + first() passe par beforeFind, donc par le
        // cloisonnement: la somme ne porte que sur l'agence courante.
        $row = (new TransitFiles())
            ->selectSum("size", "total")
            ->first();

        return (int) ($row["total"] ?? 0);
    }

    public function storageLimitBytes(): ?int
    {
        $mb = $this->limit("max_storage_mb");

        return $mb === null ? null : $mb * 1024 * 1024;
    }

    public function canStore(int $bytes): bool
    {
        $limit = $this->storageLimitBytes();

        return $limit === null or ($this->storageUsedBytes() + $bytes) <= $limit;
    }

    /** Synthèse pour l'affichage. */
    public function summary(): array
    {
        $plan = $this->plan();

        return [
            "plan" => $plan,
            "users" => ["used" => $this->usersUsed(), "limit" => $this->usersLimit()],
            "folders" => ["used" => $this->foldersThisMonth(), "limit" => $this->foldersPerMonthLimit()],
            "storage" => ["used" => $this->storageUsedBytes(), "limit" => $this->storageLimitBytes()],
        ];
    }
}
