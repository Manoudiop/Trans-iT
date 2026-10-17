<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Reprise du profil FACTURATION, devenu COMPTABLE.
 *
 * Le formulaire de création de compte n'offre plus que ADMIN, COMPTABLE et
 * OPERATEUR, et le filtre des écrans d'argent n'accepte que les deux
 * premiers. Les comptes restés en FACTURATION ne pouvaient donc ouvrir ni
 * les factures, ni la trésorerie, ni le fichier clients: le menu les leur
 * proposait, et chaque lien répondait 404.
 *
 * La reprise passe par une migration et non par une requête lancée à la
 * main: la même correction doit s'appliquer à toute base déjà en service,
 * et pas seulement à celle de développement.
 */
class RetireFacturationProfile extends Migration
{
    public function up()
    {
        $this->db->query(
            "UPDATE `users` SET `profile` = 'COMPTABLE' WHERE `profile` = 'FACTURATION'"
        );
    }

    /**
     * Volontairement sans effet.
     *
     * Rejouer l'inverse remettrait en FACTURATION des comptes qui ont
     * toujours été COMPTABLE: rien ne les distingue une fois la reprise
     * faite. Mieux vaut une descente qui ne fait rien qu'une descente qui
     * déclasse les mauvais comptes.
     */
    public function down()
    {
        // Rien.
    }
}
