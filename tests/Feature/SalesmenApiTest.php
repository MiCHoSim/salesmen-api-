<?php

namespace Tests\Feature;

use App\Models\Salesman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SalesmenApiTest extends TestCase
{
    use RefreshDatabase;

    private function createSalesman(array $attributes = []): Salesman
    {
        return Salesman::factory()->create($attributes);
    }

    private function getValidSalesmanData(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Janko',
            'last_name' => 'Hraško',
            'titles_before' => ['Ing.', 'Mgr.'],
            'titles_after' => ['PhD.'],
            'prosight_id' => (string) rand(10000, 99999),
            'email' => 'janko.hrasko' . rand(1000, 9999) . '@prosight.com',
            'phone' => '+421911222333',
            'gender' => 'm',
            'marital_status' => 'married',
        ], $overrides);
    }

    /** @test */
    public function test_get_codelists(): void
    {
        $response = $this->getJson('/api/codelists');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'marital_statuses',
                'genders',
                'titles_before',
                'titles_after'
            ]);
    }

    /** @test */
    public function test_create_salesman_success(): void
    {
        $data = $this->getValidSalesmanData();

        $response = $this->postJson('/api/salesmen', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id', 'first_name', 'last_name', 'display_name',
                    'prosight_id', 'email', 'gender'
                ]
            ])
            ->assertJsonFragment([
                'first_name' => 'Janko',
                'last_name' => 'Hraško'
            ]);

        $this->assertDatabaseHas('salesmen', ['prosight_id' => $data['prosight_id']]);
    }

    /** @test */
    public function test_create_salesman_duplicate_email(): void
    {
        $existingSalesman = $this->createSalesman(['email' => 'duplicate@prosight.com']);

        $data = $this->getValidSalesmanData([
            'email' => 'duplicate@prosight.com', // Duplicate email
            'prosight_id' => '99999'
        ]);

        $response = $this->postJson('/api/salesmen', $data);

        $response->assertStatus(409) // 409 Conflict
        ->assertJsonStructure(['errors' => [['code', 'message']]])
            ->assertJsonFragment(['code' => 'PERSON_ALREADY_EXISTS']);
    }

    /** @test */
    public function test_get_salesman_detail(): void
    {
        $salesman = $this->createSalesman();

        $response = $this->getJson("/api/salesmen/{$salesman->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id', 'self', 'first_name', 'last_name', 'display_name',
                    'prosight_id', 'email', 'gender'
                ]
            ])
            ->assertJsonFragment([
                'id' => $salesman->id,
                'first_name' => $salesman->first_name
            ]);
    }

    /** @test */
    public function test_update_salesman_success(): void
    {
        $salesman = $this->createSalesman(['first_name' => 'Old Name']);

        $updateData = [
            'first_name' => 'Updated Name',
            'last_name' => 'Updated Lastname',
            'prosight_id' => $salesman->prosight_id, // Keep same prosight_id for update
            'email' => $salesman->email, // Keep same email for update
            'gender' => 'm',
        ];

        $response = $this->putJson("/api/salesmen/{$salesman->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment(['first_name' => 'Updated Name']);

        $this->assertDatabaseHas('salesmen', [
            'id' => $salesman->id,
            'first_name' => 'Updated Name'
        ]);
    }

    /** @test */
    public function test_delete_salesman(): void
    {
        $salesman = $this->createSalesman();

        $response = $this->deleteJson("/api/salesmen/{$salesman->id}");
        $response->assertStatus(204);

        $this->assertDatabaseMissing('salesmen', ['id' => $salesman->id]);
    }

    /** @test */
    public function test_list_salesmen_with_pagination(): void
    {
        $this->createSalesman(['email' => 'test1@example.com']);
        $this->createSalesman(['email' => 'test2@example.com']);

        $response = $this->getJson('/api/salesmen?per_page=1');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email']
                ],
                'links' => ['first', 'last', 'prev', 'next']
            ])
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function test_list_salesmen_with_sorting(): void
    {
        $this->createSalesman(['first_name' => 'Adam', 'email' => 'adam@example.com']);
        $this->createSalesman(['first_name' => 'Cyril', 'email' => 'cyril@example.com']);

        $response = $this->getJson('/api/salesmen?sort=first_name');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals('Adam', $data[0]['first_name']);
    }

    /** @test */
    public function test_import_command_success(): void
    {
        Storage::fake('local');

        $csvContent = "first_name,last_name,titles_before,titles_after,prosight_id,email,phone,gender,marital_status\n";
        $csvContent .= "Test,User,Ing.,PhD.,55555,test.user@prosight.com,+421900111222,m,single";

        Storage::put('test_import.csv', $csvContent);

        $this->artisan('salesmen:import', ['file' => 'test_import.csv'])
            ->assertExitCode(0);

        $this->assertDatabaseHas('salesmen', [
            'email' => 'test.user@prosight.com',
        ]);
    }

    /** @test */
    public function test_import_command_file_not_found(): void
    {
        $this->artisan('salesmen:import', ['file' => 'non_existent.csv'])
            ->expectsOutput('File non_existent.csv not found.')
            ->assertExitCode(1);
    }

    /** @test */
    public function test_complete_salesman_lifecycle(): void
    {
        // Create
        $data = $this->getValidSalesmanData([
            'first_name' => 'Milan',
            'last_name' => 'Prostý',
            'prosight_id' => '54321',
            'email' => 'milan.prosty@prosight.com',
        ]);

        $response = $this->postJson('/api/salesmen', $data);
        $response->assertStatus(201);

        $salesmanId = $response->json('data.id');

        // Read
        $response = $this->getJson("/api/salesmen/{$salesmanId}");
        $response->assertStatus(200)
            ->assertJsonPath('data.first_name', 'Milan')
            ->assertJsonPath('data.last_name', 'Prostý');

        // Update
        $updateData = [
            'first_name' => 'Milan',
            'last_name' => 'Prostý Updated',
            'prosight_id' => '54321',
            'email' => 'milan.updated@prosight.com',
            'gender' => 'm',
        ];

        $response = $this->putJson("/api/salesmen/{$salesmanId}", $updateData);
        $response->assertStatus(200)
            ->assertJsonPath('data.last_name', 'Prostý Updated')
            ->assertJsonPath('data.email', 'milan.updated@prosight.com');

        // Delete
        $response = $this->deleteJson("/api/salesmen/{$salesmanId}");
        $response->assertStatus(204);

        // Verify deleted
        $response = $this->getJson("/api/salesmen/{$salesmanId}");
        $response->assertStatus(404);
    }
}
