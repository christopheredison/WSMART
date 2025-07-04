<?php

namespace App\Console\Commands;

use App\Models\Level;
use Illuminate\Console\Command;

class CreateLevel extends Command
{
    protected $signature = 'level:create {name} {--code=} {--description=}';
    protected $description = 'Create a new level';

    public function handle()
    {
        $name = $this->argument('name');
        $code = $this->option('code');
        $description = $this->option('description');

        $level = Level::create([
            'name' => $name,
            'code' => $code,
            'description' => $description,
        ]);

        $this->info("Level '{$level->name}' created successfully!");
        
        return Command::SUCCESS;
    }
}
