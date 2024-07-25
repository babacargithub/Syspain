<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BuildAIContext extends Command
{
    protected $signature = 'build-ai-context';
    protected $description = 'Generate context information for AI tools about the Laravel project';

    public function handle()
    {
        $filePath = base_path('ai-context.txt');
        $context = '';

        // Get Laravel version
        $context .= "I have a Laravel app with version " . app()->version() . "\n\n";

        // Define ignored directories and files
        $ignoredDirs = ['vendor', 'tests', 'storage'];
        $ignoredFiles = ['readme', 'composer-lock', 'package-lock', 'php-unit.xml', '.env'];

        // List directories to check
        $directories = [
            'Models' => 'app/Models',
            'Migrations' => 'database/migrations',
            'Routes' => 'routes',
            'Json Resources' => 'app/Http/Resources',
            'Rules' => 'app/Rules',
            'Controllers' => 'app/Http/Controllers',
            'Factories' => 'database/factories',
//            'Resources' => 'resources'
        ];

        foreach ($directories as $label => $path) {
            $context .= "Here are my $label:\n";
            $context .= $this->listFiles($path, $ignoredDirs, $ignoredFiles);
            $context .= "\n";
        }

        // Write context to file
        File::put($filePath, $context);

        $this->info('ai-context.txt file has been generated successfully.');
    }

    private function listFiles($directory, $ignoredDirs, $ignoredFiles)
    {
        $result = '';
        $files = File::allFiles($directory);
        $directories = File::directories($directory);

        // Exclude ignored directories and files
        $filteredFiles = array_filter($files, function($file) use ($ignoredFiles) {
            return !in_array(strtolower($file->getFilename()), $ignoredFiles);
        });

        foreach ($filteredFiles as $file) {
            $filename = $file->getFilename();
            $content = File::get($file);
            $result .= $filename . "\n" . $content . "\n\n";
        }

        foreach ($directories as $dir) {
            $dirname = basename($dir);
            if (!in_array($dirname, $ignoredDirs)) {
                $result .= "Directory: $dirname\n";
                $result .= $this->listFiles($dir, $ignoredDirs, $ignoredFiles);
            }
        }

        return $result;
    }


}
