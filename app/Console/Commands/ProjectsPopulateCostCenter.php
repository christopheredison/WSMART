<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProjectsPopulateCostCenter extends Command
{
    protected $signature = 'projects:populate-cost-center';
    protected $description = 'Populate the cost_center_parent column from the meta->divisisap JSON data';

    public function handle()
    {
        $this->info('Starting to populate cost_center_parent for all projects...');
        
        $projects = Project::whereNotNull('meta')->get();
        $progressBar = $this->output->createProgressBar($projects->count());
        $updatedCount = 0;

        foreach ($projects as $project) {
            try {
                $meta = is_string($project->meta) ? json_decode($project->meta, true) : $project->meta;

                if (is_array($meta) && isset($meta['divisisap']) && !empty($meta['divisisap'])) {
                    if ($project->cost_center_parent !== $meta['divisisap']) {
                        $project->cost_center_parent = $meta['divisisap'];
                        $project->save();
                        $updatedCount++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to process project ID {$project->id}: " . $e->getMessage());
                $this->warn("Could not process project ID: {$project->id}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nDone. Updated {$updatedCount} projects.");
        return 0;
    }
}