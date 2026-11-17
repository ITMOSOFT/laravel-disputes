<?php

namespace ITMOSOFT\Disputes\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ITMOSOFT\Disputes\Enums\DisputeStatus;
use ITMOSOFT\Disputes\Exceptions\InvalidDisputeTransition;
use ITMOSOFT\Disputes\Models\Dispute;
use ITMOSOFT\Disputes\Tests\Fixtures\Admin;
use ITMOSOFT\Disputes\Tests\Fixtures\Order;
use ITMOSOFT\Disputes\Tests\Fixtures\Party;

class DisputeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['test_parties', 'test_orders', 'test_admins'] as $table) {
            Schema::create($table, function (Blueprint $blueprint) {
                $blueprint->id();
                $blueprint->string('name');
            });
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    private function makeDispute(): array
    {
        $claimant = Party::query()->create(['name' => 'Buyer']);
        $respondent = Party::query()->create(['name' => 'Seller']);
        $order = Order::query()->create(['name' => 'Order 1']);

        $dispute = Dispute::query()->create([
            'reference' => 'DSP-'.uniqid(),
            'subject_type' => Order::class,
            'subject_id' => $order->id,
            'claimant_type' => Party::class,
            'claimant_id' => $claimant->id,
            'respondent_type' => Party::class,
            'respondent_id' => $respondent->id,
            'reason' => 'Item not received',
            'description' => 'The parcel has not arrived.',
        ]);

        return [$dispute, $claimant, $respondent, $order];
    }

    public function test_dispute_defaults_to_open_and_resolves_polymorphic_relations(): void
    {
        [$dispute, $claimant, $respondent, $order] = $this->makeDispute();

        $this->assertSame(DisputeStatus::Open, $dispute->status);
        $this->assertSame($claimant->id, $dispute->claimant->id);
        $this->assertSame($respondent->id, $dispute->respondent->id);
        $this->assertSame($order->id, $dispute->subject->id);
    }

    public function test_dispute_can_be_assigned_and_reviewed(): void
    {
        [$dispute] = $this->makeDispute();
        $admin = Admin::query()->create(['name' => 'Moderator']);

        $dispute->beginReview($admin);

        $this->assertSame(DisputeStatus::UnderReview, $dispute->fresh()->status);
        $this->assertSame($admin->id, $dispute->fresh()->assigned_to_id);
    }

    public function test_dispute_can_request_response_and_then_resume_review(): void
    {
        [$dispute] = $this->makeDispute();
        $dueAt = now()->addDays(3);

        $dispute->requestResponse($dueAt)->beginReview();

        $this->assertSame(DisputeStatus::UnderReview, $dispute->fresh()->status);
        $this->assertNotNull($dispute->fresh()->response_due_at);
    }

    public function test_dispute_can_be_resolved_with_summary(): void
    {
        [$dispute] = $this->makeDispute();
        $dispute->beginReview()->resolve('Refund issued.');

        $fresh = $dispute->fresh();
        $this->assertSame(DisputeStatus::Resolved, $fresh->status);
        $this->assertSame('Refund issued.', $fresh->resolution);
        $this->assertNotNull($fresh->resolved_at);
    }

    public function test_resolution_requires_non_empty_summary(): void
    {
        [$dispute] = $this->makeDispute();

        $this->expectException(\InvalidArgumentException::class);
        $dispute->resolve('  ');
    }

    public function test_invalid_transition_from_closed_is_rejected(): void
    {
        [$dispute] = $this->makeDispute();
        $dispute->close();

        $this->expectException(InvalidDisputeTransition::class);
        $dispute->beginReview();
    }

    public function test_evidence_metadata_can_be_attached(): void
    {
        [$dispute, $claimant] = $this->makeDispute();
        $evidence = $dispute->evidence()->create([
            'submitted_by_type' => Party::class,
            'submitted_by_id' => $claimant->id,
            'type' => 'delivery_receipt',
            'disk' => 'private',
            'path' => 'disputes/evidence/receipt.pdf',
            'original_name' => 'receipt.pdf',
            'mime_type' => 'application/pdf',
            'size' => 4096,
            'metadata' => ['source' => 'buyer-upload'],
        ]);

        $this->assertSame('delivery_receipt', $evidence->type);
        $this->assertSame(['source' => 'buyer-upload'], $evidence->metadata);
        $this->assertSame($claimant->id, $evidence->submittedBy->id);
    }

    public function test_claimant_can_list_filed_disputes(): void
    {
        [$dispute, $claimant] = $this->makeDispute();

        $this->assertSame($dispute->id, $claimant->filedDisputes()->first()->id);
    }

    public function test_rejected_dispute_can_be_closed(): void
    {
        [$dispute] = $this->makeDispute();
        $dispute->reject('Insufficient evidence.')->close();

        $this->assertSame(DisputeStatus::Closed, $dispute->fresh()->status);
        $this->assertNotNull($dispute->fresh()->closed_at);
    }
}
