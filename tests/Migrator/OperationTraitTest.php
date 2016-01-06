<?php namespace Orchestra\Tenanti\TestCase\Migrator;

use Mockery as m;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Orchestra\Tenanti\Migrator\OperationTrait;

class OperationTraitTest extends \PHPUnit_Framework_TestCase
{
    use OperationTrait;

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::asDefaultDatabase()
     * method.
     *
     * @test
     */
    public function testAsDefaultDatabaseMethod()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $repository = new Repository([
            'database' => [
                'default'     => 'mysql',
                'connections' => [
                    'tenant' => [
                        'database' => 'tenants',
                    ],
                ],
            ],
        ]);

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('getConfig')->with('user.connection', null)->andReturn([
                    'template' => $repository->get('database.connections.tenant'),
                    'resolver' => function (Model $entity, array $template) {
                        return array_merge($template, [
                            'database' => "tenants_{$entity->getKey()}",
                        ]);
                    },
                    'name'    => 'tenant_{id}',
                    'options' => ['only' => ['user']],
                ]);

        $this->manager = $manager;

        $model = m::mock('\Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->twice()->with('config')->andReturn($repository);
        $model->shouldReceive('getKey')->twice()->andReturn(5)
            ->shouldReceive('toArray')->once()->andReturn([
                'id' => 5,
            ]);

        $this->assertEquals('tenant_5', $this->asDefaultDatabase($model, 'tenant_{id}'));
        $this->assertEquals(['database' => 'tenants_5'], $repository->get('database.connections.tenant_5'));
        $this->assertEquals('tenant_5', $repository->get('database.default'));
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::resolveDatabaseConnection()
     * method.
     *
     * @test
     */
    public function testResolveDatabaseConnectionMethod()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $repository = new Repository([
            'database' => [
                'default'     => 'mysql',
                'connections' => [
                    'tenant' => [
                        'database' => 'tenants',
                    ],
                ],
            ],
        ]);

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('getConfig')->with('user.connection', null)->andReturn([
                'template' => $repository->get('database.connections.tenant'),
                'resolver' => function (Model $entity, array $template) {
                    return array_merge($template, [
                        'database' => "tenants_{$entity->getKey()}",
                    ]);
                },
                'name'    => 'tenant_{id}',
                'options' => [],
            ]);

        $this->manager = $manager;

        $model = m::mock('\Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->once()->with('config')->andReturn($repository);
        $model->shouldReceive('getKey')->twice()->andReturn(5)
            ->shouldReceive('toArray')->once()->andReturn([
                'id' => 5,
            ]);

        $this->assertEquals('tenant_5', $this->resolveDatabaseConnection($model, 'tenant_{id}'));
        $this->assertEquals(['database' => 'tenants_5'], $repository->get('database.connections.tenant_5'));
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::resolveModel()
     * method.
     *
     * @test
     */
    public function testResolveModelMethod()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('getConfig')->with('user.model', null)->andReturn('User')
            ->shouldReceive('getConfig')->with('user.database', null)->andReturnNull();

        $this->manager = $manager;

        $model = m::mock('\Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->once()->with('User')->andReturn($model);

        $model->shouldReceive('useWritePdo')->once()->andReturnSelf();

        $this->assertEquals($model, $this->getModel());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::resolveModel()
     * method with connection name.
     *
     * @test
     */
    public function testResolveModelMethodWithConnectionName()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('getConfig')->with('user.model', null)->andReturn('User')
            ->shouldReceive('getConfig')->with('user.database', null)->andReturn('primary');

        $this->manager = $manager;

        $model = m::mock('\Illuminate\Database\Eloquent\Model');

        $this->app->shouldReceive('make')->once()->with('User')->andReturn($model);

        $model->shouldReceive('setConnection')->once()->with('primary')->andReturnSelf()
            ->shouldReceive('useWritePdo')->once()->andReturnSelf();

        $this->assertEquals($model, $this->getModel());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::resolveModel()
     * method throw an exception when model is not an instance of
     * Eloquent.
     *
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testResolveModelMethodThrowsException()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');
        $this->driver = 'user';

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$this->app]);

        $manager->shouldReceive('getConfig')->with('user.model', null)->andReturn('User');

        $this->manager = $manager;

        $this->app->shouldReceive('make')->once()->with('User')->andReturnNull();

        $this->getModel();
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::getModelName()
     * method.
     *
     * @test
     */
    public function testGetModelNameMethod()
    {
        $app = new Container();
        $this->driver = 'user';

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('getConfig')->with('user.model', null)->andReturn('User');

        $this->manager = $manager;

        $this->assertEquals('User', $this->getModelName());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::getMigrationPath()
     * method.
     *
     * @test
     */
    public function testGetMigrationPathMethod()
    {
        $this->driver = 'user';
        $path = realpath(__DIR__);
        $app = new Container();

        $manager = m::mock('\Orchestra\Tenanti\TenantiManager', [$app]);

        $manager->shouldReceive('getConfig')->with('user.path', null)->andReturn($path);

        $this->manager = $manager;

        $this->assertEquals($path, $this->getMigrationPath());
    }

    /**
     * Test Orchestra\Tenanti\Migrator\OperationTrait::getTablePrefix()
     * method.
     *
     * @test
     */
    public function testGetTablePrefixMethod()
    {
        $this->driver = 'user';

        $this->assertEquals('user_{id}', $this->getTablePrefix());
    }
}
