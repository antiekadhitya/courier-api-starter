<?php

namespace Tests\Feature;

use App\Models\Courier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_persists_courier_to_database(): void
    {
        $payload = [
            'name' => 'Budi Agung',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'level' => 3,
            'is_active' => true,
        ];

        $this->postJson('/api/couriers', $payload)
            ->assertCreated()
            ->assertJsonPath('data.name', 'Budi Agung')
            ->assertJsonPath('data.level', 3);

        $this->assertDatabaseHas('couriers', [
            'name' => 'Budi Agung',
            'email' => 'budi@example.com',
            'level' => 3,
        ]);
    }

    public function test_store_rejects_invalid_level(): void
    {
        $this->postJson('/api/couriers', [
            'name' => 'Budi',
            'level' => 6,
        ])->assertUnprocessable()
          ->assertJsonValidationErrors('level');

        $this->assertDatabaseCount('couriers', 0);
    }

    public function test_update_persists_changes_to_database(): void
    {
        $courier = Courier::create([
            'name' => 'Budi',
            'level' => 2,
        ]);

        $this->patchJson("/api/couriers/{$courier->id}", [
            'name' => 'Budi Agung',
            'level' => 3,
        ])->assertOk()
          ->assertJsonPath('data.name', 'Budi Agung')
          ->assertJsonPath('data.level', 3);

        $this->assertDatabaseHas('couriers', [
            'id' => $courier->id,
            'name' => 'Budi Agung',
            'level' => 3,
        ]);
    }

    public function test_destroy_removes_courier_from_database(): void
    {
        $courier = Courier::create([
            'name' => 'Budi',
            'level' => 2,
        ]);

        $this->deleteJson("/api/couriers/{$courier->id}")
            ->assertOk();

        $this->assertDatabaseMissing('couriers', [
            'id' => $courier->id,
        ]);
    }

    public function test_show_returns_courier_data(): void
    {
        $courier = Courier::create([
            'name' => 'Budi',
            'level' => 2,
        ]);

        $this->getJson("/api/couriers/{$courier->id}")
            ->assertOk()
            ->assertJsonPath('id', $courier->id)
            ->assertJsonPath('name', 'Budi')
            ->assertJsonPath('level', 2);
    }

    public function test_search_matches_multiple_name_terms(): void
    {
        Courier::create(['name' => 'Budiono Hadi Agung', 'level' => 2]);
        Courier::create(['name' => 'Andi Saputra', 'level' => 3]);

        $this->getJson('/api/couriers?search=budi%20agung')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Budiono Hadi Agung');
    }

    public function test_level_filter_returns_only_requested_levels(): void
    {
        Courier::create(['name' => 'Budi', 'level' => 2]);
        Courier::create(['name' => 'Agung', 'level' => 3]);
        Courier::create(['name' => 'Andi', 'level' => 4]);

        $response = $this->getJson('/api/couriers?level=2,3');

        $response->assertOk()->assertJsonCount(2, 'data');

        $levels = collect($response->json('data'))->pluck('level')->all();

        $this->assertEqualsCanonicalizing([2, 3], $levels);
    }

    public function test_index_is_paginated_and_sorted_by_name_by_default(): void
    {
        foreach (['Charlie', 'Budi', 'Andi'] as $name) {
            Courier::create(['name' => $name, 'level' => 1]);
        }

        $this->getJson('/api/couriers?per_page=2')
            ->assertOk()
            ->assertJsonPath('current_page', 1)
            ->assertJsonPath('per_page', 2)
            ->assertJsonPath('total', 3)
            ->assertJsonPath('data.0.name', 'Andi')
            ->assertJsonPath('data.1.name', 'Budi');
    }

    public function test_index_can_sort_by_registration_date(): void
    {
        $older = Courier::create([
            'name' => 'Zebra',
            'level' => 1,
            'created_at' => now()->subMinutes(2),
        ]);

        $middle = Courier::create([
            'name' => 'Andi',
            'level' => 1,
            'created_at' => now()->subMinute(),
        ]);

        $newest = Courier::create([
            'name' => 'Budi',
            'level' => 1,
            'created_at' => now(),
        ]);

        $this->getJson('/api/couriers?sort=registered_at')
            ->assertOk()
            ->assertJsonPath('data.0.id', $newest->id)
            ->assertJsonPath('data.1.id', $middle->id)
            ->assertJsonPath('data.2.id', $older->id);
    }

    public function test_invalid_sort_is_rejected(): void
    {
        $this->getJson('/api/couriers?sort=password')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_invalid_level_filter_is_rejected(): void
    {
        $this->getJson('/api/couriers?level=2,9')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('level');
    }
}
