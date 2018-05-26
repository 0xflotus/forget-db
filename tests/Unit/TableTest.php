<?php

namespace Tests\Unit;

use App\Commands\ForgetMeNow;
use App\Services\DatabaseService;
use App\Services\ForgetDbService;
use App\Services\UtilityService;
use App\Table;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class TableTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setupTestDB();
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->deleteTestDB();
    }

    public function test_i_can_use_the_forget_service()
    {
        $config = Yaml::parse(UtilityService::stubConfig());

        (new DatabaseService([
            'driver' => 'sqlite',
            'database' => $this->testDb,
        ]))->testConnection();

        $service = new ForgetDbService($config);

        $table = current($service->getTables());

        $command = $this->createMock(ForgetMeNow::class);
        $table->setMessenger($command);

        $this->assertInstanceOf(Table::class, $table);

        $before = $table->getRows()->first();

        $table->forget($command);

        $after = $table->getRows()->first();

        $this->assertEquals($before->user_id, $after->user_id);
        $this->assertNotEquals($before->user_name, $after->user_name);
        $this->assertNotEquals($before->user_email, $after->user_email);
    }
}