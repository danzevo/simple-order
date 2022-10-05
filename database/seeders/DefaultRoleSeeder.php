<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DefaultRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        role::insert([
                        ['name' => 'administrator', 'guard_name' => 'web'],
                        ['name' => 'regular', 'guard_name' => 'web'],
                    ]);
    }
}
