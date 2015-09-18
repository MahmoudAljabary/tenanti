<?php namespace Orchestra\Tenanti\TestCase\Migrator;

use Mockery as m;
use Illuminate\Config\Repository;
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
     * Test Orchestra\Tenanti\Migrator\OperationTrait::resolveDatabaseConnection()
     * method.
     *
     * @test
     */
    public function testResolveDatabaseConnectionMethod()
    {
        $this->app = m::mock('\Illuminate\Container\Container[make]');

        $repository = new Repository([
            'database' => [
                'connections' => [
                    'tenant' => [
                        'database' => 'tenants',
                    ],
                ]
            ],
        ]);

        $this->config = [
            'model'    => 'User',
            'database' => [
                'template' => $repository->get('database.connections.tenant'),
                'resolver' => function ($id, $template) {
                    return array_merge($template, [
                        'database' => "tenants_{$id}",
                    ]);
                }
            ]
        ];

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
        $this->config = ['model' => 'User'];

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
        $this->config = ['model' => 'User', 'database' => 'primary'];

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
        $this->config = ['model' => 'User'];

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
        $this->config = ['model' => 'User'];

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
        $path = realpath(__DIR__);
        $this->config = ['path' => $path];

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
