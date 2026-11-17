<?php

namespace ITMOSOFT\Disputes\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use ITMOSOFT\Disputes\Models\Dispute;

trait HasDisputes
{
    public function filedDisputes(): MorphMany
    {
        return $this->morphMany(
            config('itmosoft-disputes.dispute_model', Dispute::class),
            'claimant'
        );
    }

    public function receivedDisputes(): MorphMany
    {
        return $this->morphMany(
            config('itmosoft-disputes.dispute_model', Dispute::class),
            'respondent'
        );
    }
}
