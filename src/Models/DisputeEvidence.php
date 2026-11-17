<?php

namespace ITMOSOFT\Disputes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DisputeEvidence extends Model
{
    protected $table = 'itmosoft_dispute_evidence';

    protected $attributes = [
        'status' => 'pending',
        'disk' => 'local',
    ];

    protected $fillable = [
        'dispute_type', 'dispute_id', 'submitted_by_type', 'submitted_by_id',
        'type', 'disk', 'path', 'original_name', 'mime_type', 'size',
        'status', 'review_notes', 'metadata',
    ];

    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
    ];

    public function dispute(): MorphTo
    {
        return $this->morphTo();
    }

    public function submittedBy(): MorphTo
    {
        return $this->morphTo('submitted_by');
    }
}
