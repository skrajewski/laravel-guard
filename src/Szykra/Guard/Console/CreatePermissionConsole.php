<?php namespace Szykra\Guard\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Szykra\Guard\Factories\PermissionFactory;

class CreatePermissionConsole extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'guard:make:permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new permission';

    /**
     * Execute the console command.
     *
     * @param PermissionFactory $factory
     * @return mixed
     */
    public function fire(PermissionFactory $factory)
    {
        $tag = $this->argument('tag');
        $name = $this->argument('name');
        $description = $this->option('description');
        $roleName = $this->option('role');

        $permission = $factory->make($tag, $name, $description);

        $this->info("Permission {$tag} has been created successfully!");

        if($roleName) {
            $roleModel = config('guard.model.role');

            try {
                $role = $roleModel::whereTag($roleName)->firstOrFail();
                $permission->roles()->attach($role);

                $this->info("Permission {$tag} has been linked with role {$roleName}");
             } catch(ModelNotFoundException $e) {
                $this->error("Cannot find role {$roleName}.");
            }
        }

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['tag', InputArgument::REQUIRED, 'Simply tag of new permission'],
            ['name', InputArgument::OPTIONAL, 'Displayed permission name'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['description', 'd', InputOption::VALUE_OPTIONAL, 'Permission description'],
            ['role', 'r', InputOption::VALUE_OPTIONAL, 'Link permission with role']
        ];
    }

}