<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The verification standard (implementation plan §5). Every item must be
 * confirmed by the verifier before a report can become verified.
 */
enum VerificationCheck: string implements HasLabel
{
    case PermalinkOrCorroborated = 'permalink_or_corroborated';
    case ProfileMatch = 'profile_match';
    case TextMatch = 'text_match';
    case Context = 'context';
    case CategoryAssigned = 'category_assigned';
    case SnapshotCaptured = 'snapshot_captured';
    case RedactionDone = 'redaction_done';
    case NotMinor = 'not_minor';

    public function label(): string
    {
        return match ($this) {
            self::PermalinkOrCorroborated => 'Permalink opens and the comment is visible, or ≥2 independent matching submissions exist',
            self::ProfileMatch => 'Comment author link resolves to the recorded profile (numeric ID/username captured)',
            self::TextMatch => 'Transcribed text matches the comment exactly (typos kept)',
            self::Context => 'Made under the linked incident and meaning is clear in context (not sarcasm or quoting to condemn)',
            self::CategoryAssigned => 'Category is assigned',
            self::SnapshotCaptured => 'Snapshot of profile name and avatar captured',
            self::RedactionDone => 'Victim, other commenters and reporter identity redacted',
            self::NotMinor => 'Profile does not appear to belong to a minor',
        };
    }

    /**
     * Returns the checks that are missing or not ticked in the given checklist.
     *
     * @param  array<string, bool>  $checklist
     * @return array<int, self>
     */
    public static function missingFrom(array $checklist): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $check): bool => ($checklist[$check->value] ?? false) !== true,
        ));
    }
}
