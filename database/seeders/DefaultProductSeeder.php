<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Master\Product;

class DefaultProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Product::insert([
                [
                    'product_code' => 'SKUSSKILNP',
                    'product_name' => 'SO Klin Pewangi',
                    'price' => '15000',
                    'discount' => '10',
                    'dimension' => '13 cm X 10 cm',
                ],
                [
                    'product_code' => 'SKUSSGIVBR',
                    'product_name' => 'Giv Biru',
                    'price' => '11000',
                    'discount' => '0',
                    'dimension' => '5 cm X 10 cm',
                ],
                [
                    'product_code' => 'SKUSSKILQD',
                    'product_name' => 'SO Klin Liquid',
                    'price' => '18000',
                    'discount' => '0',
                    'dimension' => '5 cm X 7 cm',
                ],
            ]);
    }
}
