<?php

namespace Orchestra\Tenanti\Jobs;

use Illuminate\Support\Arr;

class CreateTenant extends Job
{
    /**
     * Fire queue on creating a model.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->shouldBeFailed()) {
            return;
        }

        $database = Arr::get($this->config, 'database');
        $migrator = $this->resolveMigrator();

        if (! $this->shouldBeDelayed()) {
            $migrator->runInstall($this->model, $database);
            $migrator->runUp($this->model, $database);

            $this->delete();
        }
    }
}
