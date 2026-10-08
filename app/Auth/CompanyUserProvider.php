<?php

namespace App\Auth;

use App\Models\Scopes\CompanyScope;
use Illuminate\Auth\EloquentUserProvider;

class CompanyUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        // Resolve the identity before company scopes ask the guard for its user.
        // Ordinary model queries still enforce the authenticated company boundary.
        return parent::newModelQuery($model)->withoutGlobalScope(CompanyScope::class);
    }
}
