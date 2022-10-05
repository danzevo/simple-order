<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\UserCredit;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    public function testRegisterOwner()
    {
        $response = $this->postJson('/api/register', [
            "name"=>"mamikos",
            "email"=>"mamikos@gmail.com",
            "role"=>"owner",
            "password"=>"12345678",
            "password_confirmation"=>"12345678"
        ]);

        $response->assertStatus(201);
    }

    public function testRegisterPremium()
    {
        $response = $this->postJson('/api/register', [
            "name"=>"mamikos",
            "email"=>"mamikos@gmail.com",
            "role"=>"premium",
            "password"=>"12345678",
            "password_confirmation"=>"12345678"
        ]);

        $response->assertStatus(201);
    }

    public function testRegisterRegular()
    {
        $response = $this->postJson('/api/register', [
            "name"=>"mamikos",
            "email"=>"mamikos@gmail.com",
            "role"=>"regular",
            "password"=>"12345678",
            "password_confirmation"=>"12345678"
        ]);

        $response->assertStatus(201);
    }

    public function testLogin()
    {
        User::factory()->create([
            'email' => 'mamikos@gmail.com',
            'password' => Hash::make('12345678'),
        ]);

        $response = $this->postJson('/api/login', [
            "email"=>"mamikos@gmail.com",
            "password"=>"12345678",
        ]);

        $response->assertStatus(200);
    }

    public function testProfile() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');
        $response->assertStatus(200);
    }

    public function testLogout() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/logout');
        $response->assertStatus(200);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testValidation(array $invalidData, string $invalidParameter) {
        User::factory()->create(['email' => 'mamikos@gmail.com']);

        $validData = [
                "name"=>"mamikos",
                "email"=>"mamikos@gmail.com",
                "role"=>"owner",
                "password"=>"12345678",
                "password_confirmation"=>"12345678"
            ];

        $data = array_merge($validData, $invalidData);
        $response = $this->postJson('api/register', $data);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([$invalidParameter]);
    }

    public function validationDataProvider() {
        return [
            [['name' =>null], 'name'],
            [['email' =>null], 'email'],
            [['email' =>'mamikos@gmail.com'], 'email'],
            [['password' =>'123456'], 'password'],
            [['role' =>'superadmin'], 'role'],
        ];
    }
}
