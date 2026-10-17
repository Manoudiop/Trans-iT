<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class Users extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        $data = [];
        // Les trois profils que le formulaire de création propose. FACTURATION
        // figurait ici alors qu'il n'ouvre aucun écran d'argent: le jeu de
        // démonstration fabriquait des comptes impotents.
        $profile = ["ADMIN", "COMPTABLE", "OPERATEUR"];

        // insertBatch court-circuite les callbacks du modèle: on hache ici.
        $password = password_hash("theyankee", PASSWORD_DEFAULT);

        for ($i=0; $i < 30; $i++) {
            // insertBatch écrit en direct: tenant_id n'a plus de défaut.
            $u["tenant_id"] = 1;
            $u["name"] = $faker->firstName()." ".$faker->lastName();
            $u["email"] = "yankee".$i."@transit.com";
            $u["password"] = $password;
            $u["profile"] = $profile[rand(0,2)];
            array_push($data,$u);
        }

        $this->db->table("users")->insertBatch($data);
    }
}
