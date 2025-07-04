<?php

namespace App\Console\Commands;

use App\Models\Level;
use App\Models\Role;
use Illuminate\Console\Command;

class AssignRoleToLevel extends Command
{
    protected $signature = 'level:assign-role {level} {role}';
    protected $description = 'Assign a role to a level';

    public function handle()
    {
        $levelName = $this->argument('level');
        $roleName = $this->argument('role');

        $level = Level::where('name', $levelName)->first();
        if (!$level) {
            $this->error("Level '{$levelName}' not found!");
            return Command::FAILURE;
        }

        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("Role '{$roleName}' not found!");
            return Command::FAILURE;
        }

        $level->roles()->syncWithoutDetaching([$role->id]);

        $this->info("Role '{$role->name}' assigned to Level '{$level->name}' successfully!");
        
        return Command::SUCCESS;
    }
}
