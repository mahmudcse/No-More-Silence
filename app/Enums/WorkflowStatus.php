<?php

declare(strict_types=1);

namespace App\Enums;

use InvalidArgumentException;

enum WorkflowStatus: string implements HasLabel
{
    case Submitted = 'submitted';
    case Triaged = 'triaged';
    case InVerification = 'in_verification';
    case Verified = 'verified';
    case NeedsInfo = 'needs_info';
    case Spam = 'spam';
    case Duplicate = 'duplicate';
    case Rejected = 'rejected';
    case Retracted = 'retracted';

    /**
     * The full state machine. Keys are current statuses, values are legal targets.
     */
    private const TRANSITIONS = [
        'submitted' => ['triaged', 'spam', 'duplicate'],
        'triaged' => ['in_verification', 'spam', 'duplicate', 'rejected'],
        'in_verification' => ['verified', 'rejected', 'needs_info'],
        'needs_info' => ['in_verification'],
        'verified' => ['retracted'],
        'spam' => [],
        'duplicate' => [],
        'rejected' => [],
        'retracted' => [],
    ];

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Triaged => 'Triaged',
            self::InVerification => 'In verification',
            self::Verified => 'Verified',
            self::NeedsInfo => 'Needs information',
            self::Spam => 'Spam',
            self::Duplicate => 'Duplicate',
            self::Rejected => 'Rejected',
            self::Retracted => 'Retracted',
        };
    }

    /**
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return array_map(
            fn (string $value): self => self::from($value),
            self::TRANSITIONS[$this->value],
        );
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }

    public function assertCanTransitionTo(self $to): void
    {
        if (! $this->canTransitionTo($to)) {
            throw new InvalidArgumentException(
                "Illegal workflow transition from {$this->value} to {$to->value}."
            );
        }
    }
}
