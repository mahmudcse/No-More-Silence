<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The verification standard. Every item must be confirmed by the verifier
 * before a report can become verified.
 */
enum VerificationCheck: string implements HasLabel
{
    case PermalinkOrCorroborated = 'permalink_or_corroborated';
    case ProfileMatch = 'profile_match';
    case RedactionDone = 'redaction_done';
    case NotMinor = 'not_minor';

    public function label(): string
    {
        return match ($this) {
            self::PermalinkOrCorroborated => 'Authentic Evidence: Permalink opens or uploaded screenshots are authentic and corroborated',
            self::ProfileMatch => 'Profile Match: Comment or post author accurately matches the selected Facebook profile',
            self::RedactionDone => 'Privacy Redacted: Victim, bystander, and reporter identities are protected and redacted',
            self::NotMinor => 'Not a Minor: Profile and subject do not appear to belong to an underage minor',
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
