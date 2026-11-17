# ITMOSOFT Laravel Disputes

A reusable dispute workflow for Laravel applications. Disputes can reference any polymorphic subject (such as an order), claimant, respondent, and assigned reviewer.

## Requirements

- PHP 8.3+
- Laravel 12 or 13

## Installation

```bash
composer require itmosoft/laravel-disputes
php artisan vendor:publish --tag=disputes-config
php artisan migrate
```

Laravel package discovery registers the service provider automatically.

## Create a dispute

```php
use ITMOSOFT\Disputes\Models\Dispute;

$dispute = Dispute::query()->create([
    'reference' => (string) Str::uuid(),
    'subject_type' => $order->getMorphClass(),
    'subject_id' => $order->getKey(),
    'claimant_type' => $buyer->getMorphClass(),
    'claimant_id' => $buyer->getKey(),
    'respondent_type' => $seller->getMorphClass(),
    'respondent_id' => $seller->getKey(),
    'category' => 'delivery',
    'reason' => 'Item not received',
    'description' => 'The delivery deadline has passed.',
    'priority' => 'normal',
    'opened_at' => now(),
]);
```

`reference`, subject, claimant, respondent, and reason are required by the database/application contract. Generate a unique reference in your application. Prefer Laravel morph-map aliases (`getMorphClass()`) over hard-coded PHP class names when morph maps are configured.

## Lifecycle

```php
$dispute->beginReview($moderator);
$dispute->requestResponse(now()->addDays(3));
$dispute->beginReview($moderator);
$dispute->resolve('Refund issued.', $moderator);

// Or reject:
$dispute->reject('Claim is not supported by the submitted evidence.', $moderator);
$dispute->close();
```

Statuses: `open`, `under_review`, `awaiting_response`, `resolved`, `rejected`, `closed`.

Invalid transitions throw `InvalidDisputeTransition`. Resolution and rejection require a non-empty summary. `close()` can close an open, in-review, awaiting-response, resolved, or rejected dispute.

## Attach evidence metadata

```php
$dispute->evidence()->create([
    'submitted_by_type' => $buyer->getMorphClass(),
    'submitted_by_id' => $buyer->getKey(),
    'type' => 'delivery_receipt',
    'disk' => 'private',
    'path' => 'disputes/123/receipt.pdf',
    'original_name' => 'receipt.pdf',
    'mime_type' => 'application/pdf',
    'size' => 120000,
]);
```

The package stores metadata and a storage path only. It does not upload files, validate MIME types, scan for malware, or authorize access. Use private storage and application policies.

## Party relationships

Add `ITMOSOFT\Disputes\Concerns\HasDisputes` to a party model to expose `filedDisputes()` and `receivedDisputes()`.

## Important boundaries

This package is a workflow primitive. It does not implement chat/messaging, payment reversals, escrow, refunds, order mutation, SLA automation, notifications, immutable event history, reviewer authorization, or evidence file security. Integrate those concerns explicitly in your application. Protect every state-changing action with authorization policies.

## Testing

```bash
composer install
composer test
```

## License

MIT. See [LICENSE](LICENSE).
