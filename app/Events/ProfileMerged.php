<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\FacebookProfile;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileMerged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public FacebookProfile $primary,
        public int $mergedProfileId,
        public User $admin,
    ) {}
}
