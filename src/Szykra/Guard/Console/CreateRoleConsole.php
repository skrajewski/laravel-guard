<?php namespace Szykra\Guard\Console; 

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Szykra\Guard\Factories\RoleFactory;

class CreateRoleConsole extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'guard:make:role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new role';

    /**
     * Execute the console command.
     *
     * @param RoleFactory $factory
     * @return mixed
     */
    public function fire(RoleFactory $factory)
    {
        $tag = $this->argument('tag');
        $name = $this->argument('name');

        $factory->make($tag, $name);

        $this->info("Role {$tag} has been created successfully!");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['tag', InputArgument::REQUIRED, 'Simply tag of new role'],
            ['name', InputArgument::OPTIONAL, 'Displayed role name'],
        ];
    }

}