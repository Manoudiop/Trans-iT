<?php

namespace App\Models;

class Users extends TenantModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["name", "email", "password", "profile"];

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
    protected $beforeInsert   = ["hashPassword"];
    protected $afterInsert    = [];
    protected $beforeUpdate   = ["hashPassword"];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    protected function hashPassword($user)
    {
        if (isset($user["data"]["password"])) {
            // Garde-fou: ne jamais re-hacher une valeur déjà hachée, sous peine
            // de rendre le compte inaccessible sans message d'erreur.
            if (password_get_info($user["data"]["password"])["algo"] === null) {
                $user["data"]["password"] = password_hash($user["data"]["password"], PASSWORD_DEFAULT);
            }
        }
        return $user;
    }

    /**
     * Recherche un compte par email, toutes agences confondues.
     *
     * C'est la seule lecture non cloisonnée de l'application: à la connexion,
     * l'agence n'est pas encore connue. Elle passe délibérément par le query
     * builder brut plutôt que par un contournement générique du cloisonnement,
     * pour que l'exception reste unique, nommée et introuvable ailleurs.
     * L'unicité globale de users.email garantit au plus une ligne.
     */
    public function findForLogin(string $email): ?array
    {
        if ($email === "") {
            return null;
        }

        $row = $this->db->table($this->table)
            ->where("email", $email)
            ->get()
            ->getRowArray();

        return $row ?: null;
    }

    /**
     * Vérifie un mot de passe en clair contre le hash stocké et convertit
     * l'enregistrement au format courant si nécessaire.
     *
     * Les comptes créés avant la migration portent un SHA-1 non salé: on les
     * accepte une dernière fois, puis on les réécrit en bcrypt à la volée.
     */
    public function verifyAndRehash(array $user, string $password): bool
    {
        $stored = (string) $user["password"];

        if ($this->isLegacyHash($stored)) {
            if (!hash_equals($stored, sha1($password))) {
                return false;
            }
            $this->update($user["id"], ["password" => $password]);
            return true;
        }

        if (!password_verify($password, $stored)) {
            return false;
        }

        if (password_needs_rehash($stored, PASSWORD_DEFAULT)) {
            $this->update($user["id"], ["password" => $password]);
        }

        return true;
    }

    private function isLegacyHash(string $hash): bool
    {
        return strlen($hash) === 40 && ctype_xdigit($hash);
    }
}
