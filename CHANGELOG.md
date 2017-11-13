# Changelog

This changelog references the relevant changes (bug and security fixes) done to `orchestra/tenanti`.

## 3.4.2

Released: 2017-11-13

### Fixes

* Tenant creation and deletion job should attempt to use given model instead of querying the database again.
* Reset database connection after migration if possible.

## 3.4.1

Released: 2017-09-17

### Added

* Allow to load dynamic migration path via `Orchestra\Tenanti\Migrator\Factory::loadMigrationsFrom()`. ([#44](https://github.com/orchestral/tenanti/pull/44))

### Changes

* Re-enable to use `php artisan tenanti:queue` with `--queue` name option.

### Deprecated

* Deprecate `--force` option on `php artisan tenanti:queue`.

## 3.4.0

Released: 2017-03-29

### Changes

* Update support for Laravel Framework v5.4.
