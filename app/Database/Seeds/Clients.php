<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Faker\Factory;

class Clients extends Seeder
{
    public function run()
    {
        $data = [];
        $faker = Factory::create();

        for ($i = 0; $i < 200; $i++) {
            $client = [
                "id" => uniqid("2024"),
                'account_number' => uniqid(),
                "name" => $faker->name(),
                "email" => "yankee" . $i . "@transit.com",
                "phone" => $faker->phoneNumber(),
            ];
            array_push($data, $client);
        }

        $this->db->table("clients")->insertBatch($data);
    }
}
