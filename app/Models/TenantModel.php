<?php

namespace App\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;

/**
 * Modèle cloisonné par agence.
 *
 * Le cloisonnement est imposé par le modèle, pas par la discipline des
 * contrôleurs: il doit être impossible d'écrire une requête non cloisonnée
 * par simple oubli, puisqu'un seul oubli expose les dossiers d'une agence à
 * une autre.
 */
abstract class TenantModel extends Model
{
    public function __construct(?ConnectionInterface $db = null, ?ValidationInterface $validation = null)
    {
        // Les callbacks de cloisonnement sont préfixés à ceux du modèle
        // enfant. Un enfant qui redéclare $beforeInsert — comme Users avec
        // son hachage de mot de passe — ne peut donc pas les écraser.
        $this->beforeFind   = array_merge(["scopeTenant"], $this->beforeFind);
        $this->beforeInsert = array_merge(["stampTenant"], $this->beforeInsert);
        $this->beforeUpdate = array_merge(["scopeTenantForWrite"], $this->beforeUpdate);
        $this->beforeDelete = array_merge(["scopeTenantForWrite"], $this->beforeDelete);

        parent::__construct($db, $validation);
    }

    /**
     * countAllResults() n'est pas une méthode du modèle mais un passe-plat
     * vers le query builder: elle ne déclenche aucun callback. Sans cette
     * surcharge, les compteurs du tableau de bord compteraient les lignes de
     * toutes les agences.
     */
    public function countAllResults(bool $reset = true, bool $test = false)
    {
        return $this->builder()
            ->where($this->table . ".tenant_id", tenant_id())
            ->countAllResults($reset, $test);
    }

    protected function scopeTenant(array $event): array
    {
        $this->builder()->where($this->table . ".tenant_id", tenant_id());

        return $event;
    }

    protected function scopeTenantForWrite(array $event): array
    {
        $this->builder()->where($this->table . ".tenant_id", tenant_id());

        return $event;
    }

    /**
     * Estampille l'agence à l'insertion.
     *
     * tenant_id est volontairement absent de $allowedFields: CodeIgniter
     * applique doProtectFields() AVANT beforeInsert, donc une valeur envoyée
     * par le client est d'abord éliminée, puis remplacée ici par celle du
     * contexte. Un POST ne peut pas écrire dans une autre agence.
     */
    protected function stampTenant(array $event): array
    {
        $event["data"]["tenant_id"] = tenant_id();

        return $event;
    }
}
