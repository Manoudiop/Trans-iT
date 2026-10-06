<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Phase 2: l'email d'un utilisateur devient unique par agence.
 *
 * C'est la contrepartie de la résolution par sous-domaine. Tant que la
 * connexion se faisait par email seul, l'unicité devait être globale sous
 * peine d'ambiguïté. Le sous-domaine identifiant désormais l'agence avant la
 * connexion, la même personne peut avoir un compte chez deux agences avec la
 * même adresse — cas courant d'un consultant travaillant pour plusieurs
 * transitaires.
 *
 * L'index global est conservé sous forme d'index simple: il sert encore à la
 * connexion par le domaine racine, qui cherche les comptes correspondants
 * toutes agences confondues.
 */
class TenantScopedUserEmail extends Migration
{
    public function up()
    {
        $this->db->query(
            "ALTER TABLE `users` DROP INDEX `email`, ADD UNIQUE KEY `users_tenant_email` (`tenant_id`, `email`), ADD KEY `users_email` (`email`)"
        );
    }

    public function down()
    {
        $this->db->query(
            "ALTER TABLE `users` DROP INDEX `users_tenant_email`, DROP INDEX `users_email`, ADD UNIQUE KEY `email` (`email`)"
        );
    }
}
