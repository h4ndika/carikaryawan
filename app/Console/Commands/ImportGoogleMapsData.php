<?php

namespace App\Console\Commands;

use App\Services\GoogleMapsImportService;
use Illuminate\Console\Command;

class ImportGoogleMapsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maps:import 
                            {--file= : Specific JSON file to import}
                            {--batch-size=100 : Number of records per batch}
                            {--skip-duplicates : Skip existing records instead of updating}
                            {--default-category= : Default category ID if not found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Google Maps business data from JSON files into posts table as drafts';

    /**
     * Execute the console command.
     */
    public function handle(GoogleMapsImportService $service): int
    {
        $this->info('🗺️  Google Maps Data Importer');
        $this->info('================================');
        $this->newLine();

        // Get options
        $file = $this->option('file');
        $batchSize = (int) $this->option('batch-size');
        $skipDuplicates = $this->option('skip-duplicates');
        $defaultCategory = $this->option('default-category');

        // Configure service
        $service->setBatchSize($batchSize);
        $service->setSkipDuplicates($skipDuplicates);
        
        if ($defaultCategory) {
            $service->setDefaultCategoryId((int) $defaultCategory);
        }

        // Display configuration
        $this->info('Configuration:');
        $this->line("  • Batch size: {$batchSize}");
        $this->line("  • Skip duplicates: " . ($skipDuplicates ? 'Yes' : 'No (update)'));
        if ($defaultCategory) {
            $this->line("  • Default category ID: {$defaultCategory}");
        }
        $this->newLine();

        // Start import
        $startTime = microtime(true);

        if ($file) {
            // Import single file
            $this->info("Importing from file: {$file}");
            $this->newLine();

            if (!file_exists($file)) {
                $this->error("❌ File not found: {$file}");
                return Command::FAILURE;
            }

            $results = $service->importFromFile($file);
        } else {
            // Import from directory
            $directory = base_path('tools/google-map-extractor/output');
            $this->info("Importing from directory: {$directory}");
            $this->newLine();

            if (!is_dir($directory)) {
                $this->error("❌ Directory not found: {$directory}");
                $this->warn("Please ensure the directory exists and contains JSON files.");
                return Command::FAILURE;
            }

            $jsonFiles = glob($directory . '/*.json');
            $fileCount = count($jsonFiles);

            if ($fileCount === 0) {
                $this->warn("⚠️  No JSON files found in directory.");
                return Command::SUCCESS;
            }

            $this->info("Found {$fileCount} JSON file(s)");
            $this->newLine();

            // Create progress bar
            $progressBar = $this->output->createProgressBar($fileCount);
            $progressBar->setFormat('  %current%/%max% [%bar%] %percent:3s%% - %message%');
            $progressBar->setMessage('Starting...');
            $progressBar->start();

            $results = ['imported' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

            foreach ($jsonFiles as $jsonFile) {
                $filename = basename($jsonFile);
                $progressBar->setMessage("Processing: {$filename}");
                
                $fileResults = $service->importFromFile($jsonFile);
                
                // Aggregate results
                $results['imported'] += $fileResults['imported'];
                $results['updated'] += $fileResults['updated'];
                $results['skipped'] += $fileResults['skipped'];
                $results['failed'] += $fileResults['failed'];
                
                $progressBar->advance();
            }

            $progressBar->setMessage('Completed');
            $progressBar->finish();
            $this->newLine(2);
        }

        // Calculate execution time
        $endTime = microtime(true);
        $executionTime = round($endTime - $startTime, 2);

        // Display results
        $this->info('📊 Import Results:');
        $this->table(
            ['Status', 'Count'],
            [
                ['✅ Imported (new)', $results['imported']],
                ['🔄 Updated (existing)', $results['updated']],
                ['⊘ Skipped', $results['skipped']],
                ['❌ Failed', $results['failed']],
            ]
        );

        $totalProcessed = $results['imported'] + $results['updated'] + $results['skipped'] + $results['failed'];
        $this->newLine();
        $this->info("Total processed: {$totalProcessed} records in {$executionTime}s");

        // Show warnings if any
        if ($results['skipped'] > 0) {
            $this->newLine();
            $this->warn("⚠️  {$results['skipped']} record(s) were skipped. Check logs for details.");
        }

        if ($results['failed'] > 0) {
            $this->newLine();
            $this->error("❌ {$results['failed']} record(s) failed. Check logs for errors.");
        }

        // Success message
        if ($results['imported'] > 0 || $results['updated'] > 0) {
            $this->newLine();
            $this->info('✨ Import completed successfully!');
            $this->info('📝 All imported posts are saved as drafts. Review them in the admin panel.');
        }

        return Command::SUCCESS;
    }
}
