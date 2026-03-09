<?php

namespace ArtOfWifi\StatamicIndexnow\Models;

use Illuminate\Database\Eloquent\Model;

class IndexNowSubmission extends Model
{
    public $timestamps = false;

    protected $table = 'indexnow_submissions';

    protected $fillable = [
        'entry_id',
        'url',
        'batch_id',
        'status_code',
        'submitted_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
        ];
    }
}
