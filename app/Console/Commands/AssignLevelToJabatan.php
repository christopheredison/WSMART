<?php

namespace App\Console\Commands;

use App\Models\Jabatan;
use App\Models\Level;
use Illuminate\Console\Command;

class AssignLevelToJabatan extends Command
{
    protected $signature = 'jabatan:assign-level {jabatan} {level}';
    protected $description = 'Assign a level to a jabatan';

    public function handle()
    {
        $jabatanName = $this->argument('jabatan');
        $levelName = $this->argument('level');

        $jabatan = Jabatan::where('name', $jabatanName)->first();
        if (!$jabatan) {
            $this->error("Jabatan '{$jabatanName}' not found!");
            return Command::FAILURE;
        }

        $level = Level::where('name', $levelName)->first();
        if (!$level) {
            $this->error("Level '{$levelName}' not found!");
            return Command::FAILURE;
        }

        $jabatan->levels()->syncWithoutDetaching([$level->id]);

        $this->info("Level '{$level->name}' assigned to Jabatan '{$jabatan->name}' successfully!");
        
        return Command::SUCCESS;
    }
}
