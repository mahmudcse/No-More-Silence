<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'triage_report',
            'verify_report',
            'publish_report',
            'retract_report',
            'reject_report',
            'request_info_report',
            'redact_evidence',
            'view_reporter_identity',
            'ban_reporter',
            'handle_appeal',
            'merge_profile',
            'view_activity_log',
        ] as $name) {
            Permission::findOrCreate($name);
        }

        $superAdmin = Role::findOrCreate('super_admin');
        $superAdmin->syncPermissions(Permission::pluck('name')->all());

        $this->role('triage', [
            'ViewAny:Report', 'View:Report', 'Update:Report',
            'triage_report', 'request_info_report', 'reject_report',
            'ViewAny:FacebookProfile', 'View:FacebookProfile',
            'ViewAny:Incident', 'View:Incident', 'Create:Incident', 'Update:Incident',
        ]);

        $this->role('verifier', [
            'ViewAny:Report', 'View:Report', 'Update:Report',
            'verify_report', 'reject_report', 'request_info_report', 'merge_profile',
            'ViewAny:FacebookProfile', 'View:FacebookProfile', 'Create:FacebookProfile', 'Update:FacebookProfile',
            'ViewAny:Incident', 'View:Incident',
        ]);

        $this->role('redactor', [
            'ViewAny:Report', 'View:Report', 'Update:Report',
            'redact_evidence',
            'ViewAny:FacebookProfile', 'View:FacebookProfile',
        ]);

        $this->role('publisher', [
            'ViewAny:Report', 'View:Report',
            'publish_report', 'retract_report',
        ]);

        $this->role('appeals_officer', [
            'ViewAny:Appeal', 'View:Appeal', 'Update:Appeal', 'handle_appeal',
            'ViewAny:Report', 'View:Report',
            'ViewAny:FacebookProfile', 'View:FacebookProfile',
        ]);

        $this->role('analyst', [
            'ViewAny:Report', 'View:Report',
            'ViewAny:FacebookProfile', 'View:FacebookProfile',
            'ViewAny:Incident', 'View:Incident',
            'ViewAny:Appeal', 'View:Appeal',
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function role(string $name, array $permissions): void
    {
        Role::findOrCreate($name)->syncPermissions($permissions);
    }
}
