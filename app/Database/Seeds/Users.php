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
        $profile = ["ADMIN","FACTURATION","OPERATEUR"];

        // insertBatch court-circuite les callbacks du modèle: on hache ici.
        $password = password_hash("theyankee", PASSWORD_DEFAULT);

        for ($i=0; $i < 30; $i++) {
            $u["name"] = $faker->firstName()." ".$faker->lastName();
            $u["email"] = "yankee".$i."@transit.com";
            $u["password"] = $password;
            $u["profile"] = $profile[rand(0,2)];
            array_push($data,$u);
        }

        $this->db->table("users")->insertBatch($data);
    }
}
