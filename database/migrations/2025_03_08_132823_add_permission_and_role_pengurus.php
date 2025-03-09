<?php

use Illuminate\Database\Migrations\Migration;
use Laravolt\Platform\Models\Permission;
use Laravolt\Platform\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = Role::query()->create([
            'name' => 'Pengurus',
        ]);
        $permission = Permission::query()->create([
            'name' => 'laravolt::manage-system',
        ]);

        $role->syncPermission([$permission]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
