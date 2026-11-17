<?php

namespace ITMOSOFT\Disputes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use ITMOSOFT\Disputes\Enums\DisputeStatus;
use ITMOSOFT\Disputes\Exceptions\InvalidDisputeTransition;

class Dispute extends Model
{
    protected $table = 'itmosoft_disputes';

    protected $attributes = [
        'status' => 'open',
        'priority' => 'normal',
    ];

    protected $fillable = [
        'reference', 'subject_type', 'subject_id', 'claimant_type', 'claimant_id',
        'respondent_type', 'respondent_id', 'category', 'reason', 'description',
        'status', 'priority', 'opened_at', 'response_due_at', 'assigned_to_type',
        'assigned_to_id', 'resolved_at', 'resolution', 'closed_at', 'metadata',
    ];

    protected $casts = [
        'status' => DisputeStatus::class,
        'opened_at' => 'datetime',
        'response_due_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $dispute): void {
            $dispute->opened_at ??= now();
        });
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function claimant(): MorphTo
    {
        return $this->morphTo();
    }

    public function respondent(): MorphTo
    {
        return $this->morphTo();
    }

    public function assignee(): MorphTo
    {
        return $this->morphTo('assigned_to');
    }

    public function evidence(): MorphMany
    {
        return $this->morphMany(
            config('itmosoft-disputes.evidence_model', DisputeEvidence::class),
            'dispute'
        );
    }

    public function beginReview(?Model $assignee = null): self
    {
        $this->transitionTo(DisputeStatus::UnderReview);
        if ($assignee) {
            $this->assignee()->associate($assignee);
        }
        $this->save();

        return $this;
    }

    public function requestResponse(?\DateTimeInterface $dueAt = null): self
    {
        $this->transitionTo(DisputeStatus::AwaitingResponse);
        $this->response_due_at = $dueAt ?? now()->addDays((int) config('itmosoft-disputes.default_response_due_days', 7));
        $this->save();

        return $this;
    }

    public function resolve(string $resolution, ?Model $assignee = null): self
    {
        if (trim($resolution) === '') {
            throw new \InvalidArgumentException('A resolution summary is required.');
        }

        $this->transitionTo(DisputeStatus::Resolved);
        if ($assignee) {
            $this->assignee()->associate($assignee);
        }
        $this->resolution = $resolution;
        $this->resolved_at = now();
        $this->closed_at = null;
        $this->save();

        return $this;
    }

    public function reject(string $reason, ?Model $assignee = null): self
    {
        if (trim($reason) === '') {
            throw new \InvalidArgumentException('A rejection reason is required.');
        }

        $this->transitionTo(DisputeStatus::Rejected);
        if ($assignee) {
            $this->assignee()->associate($assignee);
        }
        $this->resolution = $reason;
        $this->resolved_at = now();
        $this->save();

        return $this;
    }

    public function close(): self
    {
        $this->transitionTo(DisputeStatus::Closed);
        $this->closed_at = now();
        $this->save();

        return $this;
    }

    protected function transitionTo(DisputeStatus $target): void
    {
        $current = $this->status;

        $allowed = match ($current) {
            DisputeStatus::Open => [
                DisputeStatus::UnderReview,
                DisputeStatus::AwaitingResponse,
                DisputeStatus::Resolved,
                DisputeStatus::Rejected,
                DisputeStatus::Closed,
            ],
            DisputeStatus::UnderReview => [
                DisputeStatus::AwaitingResponse,
                DisputeStatus::Resolved,
                DisputeStatus::Rejected,
                DisputeStatus::Closed,
            ],
            DisputeStatus::AwaitingResponse => [
                DisputeStatus::UnderReview,
                DisputeStatus::Resolved,
                DisputeStatus::Rejected,
                DisputeStatus::Closed,
            ],
            DisputeStatus::Resolved, DisputeStatus::Rejected => [DisputeStatus::Closed],
            DisputeStatus::Closed => [],
        };

        if (! in_array($target, $allowed, true)) {
            throw new InvalidDisputeTransition(
                sprintf('Cannot transition dispute from %s to %s.', $current->value, $target->value)
            );
        }

        $this->status = $target;
    }
}
