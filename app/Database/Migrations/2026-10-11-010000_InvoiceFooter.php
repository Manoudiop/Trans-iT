<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Pied de facture: lieu d'émission et conditions de règlement.
 *
 * La ville est saisie à part plutôt que déduite de l'adresse: « Km 4,5
 * Boulevard du Centenaire, Dakar » ne se découpe pas de façon fiable, et la
 * mention « Fait à … » figure sur chaque facture.
 *
 * Les conditions de règlement varient d'une maison à l'autre — délai,
 * pénalités, coordonnées bancaires. Elles appartiennent donc à l'agence et
 * non au code.
 */
class InvoiceFooter extends Migration
{
    public function up()
    {
        $this->forge->addColumn("tenants", [
            "city" => [
                "type" => "VARCHAR",
                "constraint" => 100,
                "null" => true,
                "after" => "address",
            ],
            "payment_terms" => [
                "type" => "TEXT",
                "null" => true,
                "after" => "agreement_number",
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn("tenants", ["city", "payment_terms"]);
    }
}
