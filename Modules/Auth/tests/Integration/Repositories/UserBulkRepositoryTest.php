<?php

namespace Modules\Auth\Tests\Integration\Repositories;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Auth\Models\User;
use Modules\Auth\Repositories\UserBulkRepository;
use Tests\TestCase;

class UserBulkRepositoryTest extends TestCase
{
    use LazilyRefreshDatabase;

    private UserBulkRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserBulkRepository();
    }

    public function test_find_by_ids_returns_correct_users()
    {
        $users = User::factory()->count(3)->create();
        $ids = $users->pluck('id')->toArray();

        $results = $this->repository->findByIds($ids);

        $this->assertCount(3, $results);
        foreach ($users as $user) {
            $this->assertTrue($results->contains('id', $user->id));
        }
    }

    public function test_bulk_update_status()
    {
        $users = User::factory()->count(2)->create(['status' => 'inactive']);
        $ids = $users->pluck('id')->toArray();

        $count = $this->repository->bulkUpdateStatus($ids, 'active');

        $this->assertEquals(2, $count);
        $this->assertEquals('active', $users->first()->fresh()->status->value);
    }
}
