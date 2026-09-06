<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportActivity extends Model
{
    protected $fillable = ['report_id', 'user_id', 'event', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
