<?php

declare(strict_types=1);

namespace App\Console\Commands\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait FindsAssociatedModel
{
    /**
     * Find a model by ID or a specified column.
     *
     * @param  class-string<Model>  $modelClass
     * @param  string  $identifier
     * @param  string  $searchColumn
     * @param  \Closure|null  $queryCallback
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function findModel(string $modelClass, string $identifier, string $searchColumn = 'name', \Closure $queryCallback = null): Model
    {
        $query = $modelClass::query();

        if ($queryCallback) {
            $queryCallback($query);
        }

        if (is_numeric($identifier)) {
            return $query->findOrFail((int) $identifier);
        }

        return $query->where($searchColumn, $identifier)->firstOrFail();
    }
}
