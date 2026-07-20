<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        app(AuditLogger::class)->model($model, 'created');
    }

    public function updated(Model $model): void
    {
        app(AuditLogger::class)->model($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        app(AuditLogger::class)->model($model, 'deleted');
    }

    public function restored(Model $model): void
    {
        app(AuditLogger::class)->model($model, 'restored');
    }
}
