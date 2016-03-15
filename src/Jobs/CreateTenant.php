<?php namespace Orchestra\Tenanti\Jobs;

use Illuminate\Support\Arr;

class CreateTenant extends Tenant
{
    /**
     * Fire queue on creating a model.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 3) {
            return $this->failed();
        }

        $database = Arr::get($this->config, 'database');
        $migrator = $this->resolveMigrator();

        if (is_null($this->model)) {
            return $this->release(10);
        }

        $migrator->runInstall($this->model, $database);
        $migrator->runUp($this->model, $database);

        $this->delete();
    }
}
