<?php

namespace App\Console\Commands;

use App\Models\Jabatan;
use Illuminate\Console\Command;

class CreateJabatan extends Command
{
    protected $signature = 'jabatan:create {name} {--code=} {--description=}'; 
    protected $description = 'Create a new jabatan';

    public function handle()
    {
        $name = $this->argument('name');
        $code = $this->option('code');
        $description = $this->option('description');

        $jabatan = Jabatan::create([
            'name' => $name,
            'code' => $code,
            'description' => $description,
        ]);

        $this->info("Jabatan '{$jabatan->name}' created successfully!");
        
        return Command::SUCCESS;
    }
}
