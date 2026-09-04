<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Masmerise\Toaster\Toaster;

if (!function_exists('toaster')) {
    function toaster()
    {
        return app(Toaster::class);
    }
}

if (!function_exists('languagesList')) {
    /**
     * Return the list of locales currently installed in the translations
     * `strings` table (i.e. the columns that hold translated values, minus
     * the housekeeping ones).
     *
     * Used by the language manager admin views (resources/views/vendor/langs/*)
     * — specifically by the "Add New Language" dropdown to filter out locales
     * that are already installed. The published view assumes a global helper
     * by this name exists; the elseyyid/laravel-json-mysql-locations-manager
     * package does not ship one, so we provide it here.
     *
     * The result is memoized for the lifetime of the request: the
     * language-manager `home` view calls this helper once per supported
     * locale (and again per installed locale row), which means without
     * caching we'd re-issue a `SHOW COLUMNS` query dozens of times per
     * request. The cache resets at the end of every PHP process, so schema
     * changes from `php artisan elseyyid:location:install` (run from a
     * separate process) are picked up on the next request automatically.
     *
     * Returns an empty array on fresh installs where the `strings` table
     * has not been created yet so the dropdown still renders cleanly.
     */
    function languagesList(): array
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        if (!Schema::hasTable('strings')) {
            return $cache = [];
        }

        return $cache = collect(DB::getSchemaBuilder()->getColumnListing('strings'))
            ->reject(fn ($column) => in_array($column, ['code', 'en', 'created_at', 'updated_at'], true))
            ->values()
            ->all();
    }
}

if (!function_exists('json_encode_prettify')) {
    /**
     * Pretty-print a value as JSON for writing language files.
     *
     * The elseyyid/laravel-json-mysql-locations-manager package defines this
     * helper in src/helpers/helpers.php and loads it at runtime from its
     * service provider via File::glob(...)->require_once. That runtime loading
     * is unreliable on optimized production deployments (cached config/routes,
     * the optimized class loader, OPcache), which caused
     * "Call to undefined function ...json_encode_prettify()" when admins hit the
     * "Publish all"/"Generate JSON" actions on the server while it worked
     * locally.
     *
     * Defining it here means Composer's `files` autoload (see composer.json)
     * loads it during framework bootstrap — before any service provider runs —
     * so the function is always available regardless of the package's runtime
     * glob. Both this definition and the package's are guarded with
     * function_exists(), so whichever loads first wins and there is no conflict.
     *
     * @param  mixed  $data
     */
    function json_encode_prettify($data): string|false
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
