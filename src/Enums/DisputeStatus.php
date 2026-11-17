<?php

namespace ITMOSOFT\Disputes\Enums;

enum DisputeStatus: string
{
    case Open = 'open';
    case UnderReview = 'under_review';
    case AwaitingResponse = 'awaiting_response';
    case Resolved = 'resolved';
    case Rejected = 'rejected';
    case Closed = 'closed';

    public function isFinal(): bool
    {
        return in_array($this, [self::Resolved, self::Rejected, self::Closed], true);
    }
}
