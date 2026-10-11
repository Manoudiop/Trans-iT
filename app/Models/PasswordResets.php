<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Jetons de réinitialisation de mot de passe.
 *
 * Ce modèle n'étend pas TenantModel, et c'est délibéré: le jeton est lu
 * avant que l'agence soit connue — un lien reçu par courriel peut être
 * ouvert depuis n'importe quelle adresse. C'est le jeton lui-même, tiré de
 * 32 octets aléatoires, qui tient lieu de secret; l'agence est ensuite
 * déduite de la ligne trouvée, jamais de ce que demande le visiteur.
 *
 * Le jeton en clair n'existe qu'une fois, dans le courriel. La base n'en
 * garde que l'empreinte SHA-256.
 */
class PasswordResets extends Model
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ["tenant_id", "user_id", "token_hash", "expires_at", "used_at"];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Durée de validité d'un lien, en minutes. */
    public const DUREE_MINUTES = 60;

    /** Délai minimal entre deux demandes pour un même compte, en secondes. */
    public const DELAI_ENTRE_DEMANDES = 120;

    public static function empreinte(string $jeton): string
    {
        return hash("sha256", $jeton);
    }

    /**
     * Crée un jeton pour ce compte et renvoie sa valeur en clair.
     *
     * Les jetons antérieurs encore valides sont marqués comme consommés:
     * une nouvelle demande doit invalider le lien précédent, sans quoi un
     * courriel intercepté resterait utilisable.
     */
    public function emettre(int $tenantId, int $userId): string
    {
        $this->consommerLesPrecedents($userId);

        $jeton = bin2hex(random_bytes(32));

        $this->insert([
            "tenant_id" => $tenantId,
            "user_id" => $userId,
            "token_hash" => self::empreinte($jeton),
            "expires_at" => date("Y-m-d H:i:s", time() + self::DUREE_MINUTES * 60),
        ]);

        return $jeton;
    }

    /**
     * Ligne correspondant à ce jeton, si elle est encore utilisable.
     *
     * @return array<string, mixed>|null
     */
    public function valide(string $jeton): ?array
    {
        if ($jeton === "") {
            return null;
        }

        $ligne = $this->where("token_hash", self::empreinte($jeton))
            ->where("used_at", null)
            ->where("expires_at >=", date("Y-m-d H:i:s"))
            ->first();

        return $ligne ?: null;
    }

    public function consommer(int $id): void
    {
        $this->update($id, ["used_at" => date("Y-m-d H:i:s")]);
    }

    /**
     * Vrai si une demande trop récente existe déjà pour ce compte.
     *
     * Sans ce frein, le formulaire servirait à inonder la boîte d'un tiers.
     */
    public function demandeTropRecente(int $userId): bool
    {
        $depuis = date("Y-m-d H:i:s", time() - self::DELAI_ENTRE_DEMANDES);

        return $this->where("user_id", $userId)
            ->where("created_at >=", $depuis)
            ->countAllResults() > 0;
    }

    private function consommerLesPrecedents(int $userId): void
    {
        $this->where("user_id", $userId)
            ->where("used_at", null)
            ->set(["used_at" => date("Y-m-d H:i:s")])
            ->update();
    }
}
