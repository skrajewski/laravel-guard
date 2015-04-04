<?php namespace Szykra\Guard\Console; 

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Szykra\Guard\Factories\RoleFactory;

class LinkPermissionWithRoleConsole extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'guard:grant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add permission for role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $role = $this->argument('role');
        $permission = $this->argument('permission');

        $roleClass = config('guard.model.role');
        $permissionClass = config('guard.model.permission');

        $roleModel = $roleClass::whereTag($role)->firstOrFail();
        $permissionModel = $permissionClass::whereTag($permission)->firstOrFail();

        $roleModel->permissions()->attach($permissionModel);

        $this->info("Successfully linking role {$role} with permission {$permission}!");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['role', InputArgument::REQUIRED, 'Role tag'],
            ['permission', InputArgument::REQUIRED, 'Permission tag'],
        ];
    }

}