<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up a user with a specific role and act as them.
     */
    protected function actAsRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $user = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($user);
        
        return $user;
    }
}
