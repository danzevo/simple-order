<?php

namespace Tests\Feature\Kos;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\User\UserCredit;
use App\Models\Kos\{
    Kos,
    Address
};

class KostListTest extends TestCase
{
    public function testWithoutFilter() {
        $user = User::factory()->create();

        Kos::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/kost-list');
        $response->assertStatus(200);
    }

    public function testNameOrPriceFilter() {
        $user = User::factory()->create();

        Kos::factory()->create(['name' => 'Type A', 'price' => '700000', 'user_id' => $user->id]);
        Kos::factory()->create(['name' => 'Type B', 'price' => '500000', 'user_id' => $user->id]);
        Kos::factory()->create(['name' => 'Type C', 'price' => '300000', 'user_id' => $user->id]);

        $response = $this->getJson('/api/kost-list?q=A');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?q=b');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?q=700000');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?q=500000');
        $response->assertStatus(200);
    }

    public function testLocationFilter() {
        $user = User::factory()->create();

        $kos1 = Kos::factory()->create(['name' => 'Type A', 'price' => '700000', 'user_id' => $user->id]);
        $kos2 = Kos::factory()->create(['name' => 'Type B', 'price' => '500000', 'user_id' => $user->id]);
        $kos3 = Kos::factory()->create(['name' => 'Type C', 'price' => '300000', 'user_id' => $user->id]);

        Address::factory()->create([
                                'kos_id' => $kos1->id,
                                'province' => 'jawa barat',
                                'city' => 'bandung',
                                'district' => 'kiaracondong',
                                'address' => 'jl. kiaracondong',
                            ]);
        Address::factory()->create([
                                'kos_id' => $kos2->id,
                                'province' => 'jawa barat',
                                'city' => 'bandung',
                                'district' => 'dago',
                                'address' => 'jl. dago',
                            ]);
        Address::factory()->create([
                                'kos_id' => $kos3->id,
                                'province' => 'jawa tengah',
                                'city' => 'yogyakarta',
                                'district' => 'malioboro',
                                'address' => 'jl. malioboro',
                            ]);

        $response = $this->getJson('/api/kost-list?location=kiaracondong');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?location=yogyakarta');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?location=dago');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?location=malioboro');
        $response->assertStatus(200);
    }

    public function testPriceSort() {
        $user = User::factory()->create();

        Kos::factory()->create(['name' => 'Type A', 'price' => '700000', 'user_id' => $user->id]);
        Kos::factory()->create(['name' => 'Type B', 'price' => '500000', 'user_id' => $user->id]);
        Kos::factory()->create(['name' => 'Type C', 'price' => '300000', 'user_id' => $user->id]);

        $response = $this->getJson('/api/kost-list?sort_by=price&sort_type=asc');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?sort_by=price&sort_type=desc');
        $response->assertStatus(200);
    }

    public function testPagination() {
        $user = User::factory()->create();

        Kos::factory()->count(10)->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/kost-list?page=1&perPage=5');
        $response->assertStatus(200);

        $response = $this->getJson('/api/kost-list?page=2&perPage=5');
        $response->assertStatus(200);
    }

    public function testDetailList() {
        $user = User::factory()->create();

        $kos = Kos::factory()
                    ->hasAddress()->hasFacility()
                    ->hasRoom()->hasKosImage(2)
                    ->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/kost-list/'.enkrip($kos->id));
        $response->assertStatus(200);
    }

    public function testRoomAvailibilityAsGuest() {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $user1 = User::factory()->create();
        $user1->assignRole('premium');
        UserCredit::create(['user_id' => $user1->id, 'credit' => 40]);
        $user2 = User::factory()->create();
        $user2->assignRole('regular');
        UserCredit::create(['user_id' => $user2->id, 'credit' => 20]);

        $kos = Kos::factory()
                    ->hasAddress()->hasFacility()
                    ->hasRoom()->hasKosImage(2)
                    ->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/room-availibility/'.enkrip($kos->id));
        $response->assertStatus(401);
    }

    public function testRoomAvailibility() {
        $user = User::factory()->create();
        $user->assignRole('owner');
        $user1 = User::factory()->create();
        $user1->assignRole('premium');
        UserCredit::create(['user_id' => $user1->id, 'credit' => 40]);
        $user2 = User::factory()->create();
        $user2->assignRole('regular');
        UserCredit::create(['user_id' => $user2->id, 'credit' => 20]);

        $kos = Kos::factory()
                    ->hasAddress()->hasFacility()
                    ->hasRoom()->hasKosImage(2)
                    ->create(['user_id' => $user->id]);

        $response = $this->actingAs($user1)->getJson('/api/room-availibility/'.enkrip($kos->id));
        $response->assertStatus(200);

        $response = $this->actingAs($user2)->getJson('/api/room-availibility/'.enkrip($kos->id));
        $response->assertStatus(200);
    }

    public function testDashboardAsGuest() {
        $response = $this->getJson('/api/owner-kos-dashboard/');
        $response->assertStatus(401);
    }

    public function testDashboardAsUser() {
        $user = User::factory()->create();
        $user->assignRole('premium');

        $kos = Kos::factory()
                    ->hasRoom(2)
                    ->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/owner-kos-dashboard');
        $response->assertStatus(403);
    }

    public function testDashboard() {
        $user = User::factory()->hasUserCredit()->create();
        $user->assignRole('owner');

        $kos = Kos::factory()
                    ->hasRoom(2)
                    ->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/owner-kos-dashboard');
        $response->assertStatus(200);
    }
}
