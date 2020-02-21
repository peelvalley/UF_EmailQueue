<?php

namespace UserFrosting\Sprinkle\EmailQueue\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

class EmailQueuePermissions extends BaseSeed
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->validateMigrationDependencies([
            '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
            '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
        ]);

        // Get and save permissions
        $permissions = $this->getPermissions();
        $this->savePermissions($permissions);
        // Add default mappings to permissions
        $this->syncPermissionsRole($permissions);
    }

    /**
     * @return array Permissions to seed
     */
    protected function getPermissions()
    {
        return [
            'view_mailing_queue' => new Permission([
                'slug' => 'view_mailing_queue',
                'name' => 'View mailing queue contents',
                'conditions' => 'always()',
                'description' => 'Enables user to view mailing queue contents'
            ]),

        ];
    }

    /**
     * Save permissions.
     *
     * @param array $permissions
     */
    protected function savePermissions(array &$permissions)
    {
        foreach ($permissions as $slug => $permission) {
            // Trying to find if the permission already exist
            $existingPermission = Permission::where(['slug' => $permission->slug, 'conditions' => $permission->conditions])->first();
            // Don't save if already exist, use existing permission reference
            // otherwise to re-sync permissions and roles
            if ($existingPermission == null) {
                $permission->save();
            } else {
                $permissions[$slug] = $existingPermission;
            }
        }
    }

    /**
     * Sync permissions with default roles.
     *
     * @param array $permissions
     */
    protected function syncPermissionsRole(array $permissions)
    {

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->syncWithoutDetaching([
                $permissions['view_mailing_queue']->id
            ]);
        }
    }
}