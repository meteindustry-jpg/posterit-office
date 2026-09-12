<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

#[Signature('db:backup {--keep=30 : Number of days of backups to retain}')]
#[Description('Create an automated, timestamped backup of the production SQLite database.')]
class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup {--keep=30 : Number of days of backups to retain}';

    protected $description = 'Create an automated, timestamped backup of the production SQLite database.';

    public function handle(): int
    {
        $dbPath = database_path('database.sqlite');

        if (! file_exists($dbPath)) {
            $this->error("Database file not found at: {$dbPath}");

            return self::FAILURE;
        }

        $backupDir = base_path('../backups/databases');
        if (! is_dir($backupDir)) {
            $backupDir = storage_path('app/backups');
        }

        if (! file_exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y_m_d_His');
        $backupFile = "{$backupDir}/backup_{$timestamp}.sqlite";

        if (copy($dbPath, $backupFile)) {
            $sizeKb = round(filesize($backupFile) / 1024, 2);
            $this->info("✓ Database backup created successfully: {$backupFile} ({$sizeKb} KB)");

            // Clean up backups older than retention days
            $keepDays = (int) $this->option('keep') ?: 30;
            $cutoff = now()->subDays($keepDays)->getTimestamp();

            $files = glob("{$backupDir}/backup_*.sqlite");
            $deleted = 0;
            if ($files) {
                foreach ($files as $file) {
                    if (filemtime($file) < $cutoff) {
                        @unlink($file);
                        $deleted++;
                    }
                }
            }

            if ($deleted > 0) {
                $this->info("✓ Cleaned up {$deleted} old backup files older than {$keepDays} days.");
            }

            return self::SUCCESS;
        }

        $this->error('Failed to copy database file.');

        return self::FAILURE;
    }
}
