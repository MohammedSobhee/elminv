<?php
namespace App\Traits;

use App\Permission;
use App\Role;

trait PermissionTrait {

    protected function hasPermission($permission) {
        return (bool) $this->permissions->where('slug', $permission->slug)->count();
    }

    public function roles() {
        return $this->belongsToMany(Role::class, 'users_roles');
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'users_permissions');

    }

    public function hasRole(...$roles) {
        foreach ($roles as $role) {
            if ($this->roles->contains('slug', $role)) {
                return true;
            }
        }
        return false;
    }

    public function noRole(...$roles) {
        $matched = 0;
        foreach ($roles as $role) {
            if ($this->roles->contains('slug', $role)) {
                $matched = 1;
                break;
            }
        }
        if ($matched == 1) {
            //user is in the group return false
            return false;
        } else {
            return true;
        }
    }
    public function hasPermissionThroughRole($permission) {
        foreach ($permission->roles as $role) {
            if ($this->roles->contains($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasPermissionTo($permission) {
        return $this->hasPermissionThroughRole($permission) || $this->hasPermission($permission);
    }

    public function givePermissionsTo(...$permissions) {
        $permissions = $this->getAllPermissions($permissions);
        dd($permissions);
        if ($permissions === null) {
            return $this;
        }
        $this->permissions()->saveMany($permissions);
        return $this;
    }

    public function deletePermissions(...$permissions) {
        $permissions = $this->getAllPermissions($permissions);
        $this->permissions()->detach($permissions);
        return $this;
    }
}
