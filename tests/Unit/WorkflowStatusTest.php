<?php

declare(strict_types=1);

use App\Enums\WorkflowStatus;

it('allows the documented workflow transitions', function (): void {
    $transitions = [
        [WorkflowStatus::Submitted, WorkflowStatus::Triaged],
        [WorkflowStatus::Submitted, WorkflowStatus::Spam],
        [WorkflowStatus::Submitted, WorkflowStatus::Duplicate],
        [WorkflowStatus::Triaged, WorkflowStatus::InVerification],
        [WorkflowStatus::Triaged, WorkflowStatus::Spam],
        [WorkflowStatus::Triaged, WorkflowStatus::Duplicate],
        [WorkflowStatus::Triaged, WorkflowStatus::Rejected],
        [WorkflowStatus::InVerification, WorkflowStatus::Verified],
        [WorkflowStatus::InVerification, WorkflowStatus::Rejected],
        [WorkflowStatus::InVerification, WorkflowStatus::NeedsInfo],
        [WorkflowStatus::NeedsInfo, WorkflowStatus::InVerification],
        [WorkflowStatus::Verified, WorkflowStatus::Retracted],
    ];

    foreach ($transitions as [$from, $to]) {
        expect($from->canTransitionTo($to))->toBeTrue();
    }
});

it('rejects illegal workflow transitions', function (): void {
    $transitions = [
        [WorkflowStatus::Submitted, WorkflowStatus::Verified],
        [WorkflowStatus::Triaged, WorkflowStatus::Retracted],
        [WorkflowStatus::InVerification, WorkflowStatus::Triaged],
        [WorkflowStatus::Verified, WorkflowStatus::Rejected],
        [WorkflowStatus::Retracted, WorkflowStatus::Submitted],
    ];

    foreach ($transitions as [$from, $to]) {
        expect(fn () => $from->assertCanTransitionTo($to))
            ->toThrow(InvalidArgumentException::class);
    }
});
