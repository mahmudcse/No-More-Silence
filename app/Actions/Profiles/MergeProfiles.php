<?php

declare(strict_types=1);

namespace App\Actions\Profiles;

use App\Events\ProfileMerged;
use App\Models\FacebookProfile;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

final class MergeProfiles
{
    public function handle(FacebookProfile $primary, FacebookProfile $secondary, User $admin): FacebookProfile
    {
        if ($primary->is($secondary)) {
            throw new DomainException('A profile cannot be merged into itself.');
        }

        DB::transaction(function () use ($primary, $secondary): void {
            $secondary->aliases()->update(['facebook_profile_id' => $primary->id]);
            $secondary->snapshots()->update(['facebook_profile_id' => $primary->id]);
            $secondary->reports()->update(['facebook_profile_id' => $primary->id]);
            $secondary->appeals()->update(['facebook_profile_id' => $primary->id]);

            $secondary->delete();
        });

        ProfileMerged::dispatch($primary, $secondary->id, $admin);

        return $primary;
    }
}
