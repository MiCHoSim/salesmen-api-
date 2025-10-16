<?php

namespace Tests\Unit;

use App\Exceptions\SalesmanAlreadyExistsException;
use App\Models\Salesman;
use App\Repositories\SalesmanRepository;
use App\Services\SalesmanService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SalesmanServiceTest extends TestCase
{
    use RefreshDatabase;

    private SalesmanService $salesmanService;

    protected function setUp(): void
    {
        parent::setUp();
        $repository = new SalesmanRepository(new Salesman());
        $this->salesmanService = new SalesmanService($repository);
    }

    /** @test */
    public function test_create_salesman_success(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'prosight_id' => '12345',
            'email' => 'test@prosight.com',
            'gender' => 'm',
            'marital_status' => 'single',
        ];

        $salesman = $this->salesmanService->createSalesman($data);

        $this->assertInstanceOf(Salesman::class, $salesman);
        $this->assertEquals('Test', $salesman->first_name);
        $this->assertEquals('12345', $salesman->prosight_id);
        $this->assertEquals('Test User', $salesman->display_name);
    }

    /** @test */
    public function test_create_salesman_duplicate_prosight_id(): void
    {
        Salesman::factory()->create(['prosight_id' => '11111']);

        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'prosight_id' => '11111', // Duplicitný
            'email' => 'new@prosight.com',
            'gender' => 'm',
        ];

        $this->expectException(SalesmanAlreadyExistsException::class);
        $this->expectExceptionMessage('Salesman with such prosight_id 11111 is already registered.');

        $this->salesmanService->createSalesman($data);
    }

    /** @test */
    public function test_create_salesman_duplicate_email(): void
    {
        Salesman::factory()->create(['email' => 'duplicate@prosight.com']);

        $data = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'prosight_id' => '99999',
            'email' => 'duplicate@prosight.com', // Duplicitný email
            'gender' => 'm',
        ];

        $this->expectException(SalesmanAlreadyExistsException::class);
        $this->expectExceptionMessage('Salesman with such email duplicate@prosight.com is already registered.');

        $this->salesmanService->createSalesman($data);
    }

    /** @test */
    public function test_get_salesman_success(): void
    {
        $salesman = Salesman::factory()->create();

        $found = $this->salesmanService->getSalesman($salesman->id);

        $this->assertEquals($salesman->id, $found->id);
        $this->assertEquals($salesman->email, $found->email);
    }

    /** @test */
    public function test_get_salesman_not_found(): void
    {
        $invalidUuid = '00000000-0000-0000-0000-000000000000';

        $this->expectException(ModelNotFoundException::class);
        $this->salesmanService->getSalesman($invalidUuid);
    }

    /** @test */
    public function test_update_salesman_success(): void
    {
        $salesman = Salesman::factory()->create(['first_name' => 'Old Name']);

        $updated = $this->salesmanService->updateSalesman($salesman->id, [
            'first_name' => 'New Name'
        ]);

        $this->assertEquals('New Name', $updated->first_name);
        $this->assertEquals($salesman->id, $updated->id);
    }

    /** @test */
    public function test_delete_salesman_success(): void
    {
        $salesman = Salesman::factory()->create();

        $this->salesmanService->deleteSalesman($salesman->id);

        $this->assertDatabaseMissing('salesmen', ['id' => $salesman->id]);
    }

    /** @test */
    public function test_list_salesmen_pagination(): void
    {
        Salesman::factory()->count(15)->create();

        $result = $this->salesmanService->listSalesmen(perPage: 10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(10, $result->items());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /** @test */
    public function test_list_salesmen_with_search(): void
    {
        Salesman::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);
        Salesman::factory()->create(['first_name' => 'Jane', 'last_name' => 'Smith']);

        $result = $this->salesmanService->listSalesmen(
            filters: ['search' => 'John'],
            perPage: 10
        );

        $this->assertCount(1, $result->items());
        $this->assertEquals('John', $result->items()[0]->first_name);
    }

    /** @test */
    public function test_list_salesmen_with_sorting(): void
    {
        Salesman::factory()->create(['first_name' => 'Adam', 'created_at' => now()->subDay()]);
        Salesman::factory()->create(['first_name' => 'Cyril', 'created_at' => now()]);

        $result = $this->salesmanService->listSalesmen(
            sort: 'first_name',
            perPage: 10
        );

        $this->assertEquals('Adam', $result->items()[0]->first_name);
    }
}
