<?php

return [
    'dispute_model' => ITMOSOFT\Disputes\Models\Dispute::class,
    'evidence_model' => ITMOSOFT\Disputes\Models\DisputeEvidence::class,
    'default_response_due_days' => 7,
    'statuses' => [
        'open',
        'under_review',
        'awaiting_response',
        'resolved',
        'rejected',
        'closed',
    ],
];
