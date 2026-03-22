<?php

namespace App\Livewire\SuperadminSettings;

use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupSettings extends Component
{
    use LivewireAlert;

    public $backups = [];
    public bool $isGenerating = false;

    public function mount()
    {
        $this->loadBackups();
    }

    public function loadBackups(): void
    {
        $this->backups = [];
        $backupPath = storage_path('app/backups');

        if (!is_dir($backupPath)) {
            return;
        }

        $files = glob($backupPath . '/backup_*.zip');
        rsort($files); // Most recent first

        foreach ($files as $file) {
            $this->backups[] = [
                'name' => basename($file),
                'size' => $this->formatSize(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
            ];
        }
    }

    public function generateBackup(): void
    {
        $this->isGenerating = true;

        try {
            $backupDir = storage_path('app/backups');
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $zipPath = $backupDir . "/backup_{$timestamp}.zip";
            $sqlPath = $backupDir . "/database_{$timestamp}.sql";

            // 1. Database dump
            $this->dumpDatabase($sqlPath);

            // 2. Create ZIP with SQL + uploaded files
            $this->createZipBackup($zipPath, $sqlPath);

            // 3. Clean up the SQL file (already inside ZIP)
            if (file_exists($sqlPath)) {
                unlink($sqlPath);
            }

            $this->loadBackups();

            $this->alert('success', __('modules.backup.backupCreated'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', __('modules.backup.backupFailed') . ': ' . $e->getMessage(), [
                'toast' => false,
                'position' => 'center',
                'showCancelButton' => true,
                'cancelButtonText' => __('app.close'),
            ]);
        } finally {
            $this->isGenerating = false;
        }
    }

    protected function dumpDatabase(string $outputPath): void
    {
        $config = config('database.connections.mysql');

        // Find mysqldump binary - check common paths
        $mysqldump = 'mysqldump';
        $commonPaths = ['/usr/bin/mysqldump', '/usr/local/bin/mysqldump', '/usr/local/mysql/bin/mysqldump'];
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                $mysqldump = $path;
                break;
            }
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $user = $config['username'] ?? 'root';
        $pass = $config['password'] ?? '';
        $db   = $config['database'];

        // Build command as shell string to handle password safely
        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s --databases %s --no-tablespaces --skip-lock-tables --result-file=%s 2>&1',
            escapeshellarg($mysqldump),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass !== '' ? '--password=' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($outputPath)
        );

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(300);
        $process->run();

        $error = $process->getErrorOutput() ?: $process->getOutput();

        // Filter out the password warning - it's not an actual error
        $filteredError = trim(preg_replace('/.*Using a password on the command line interface can be insecure.*/i', '', $error));

        if (!$process->isSuccessful() && !empty($filteredError)) {
            throw new \RuntimeException('mysqldump failed: ' . $filteredError);
        }

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('mysqldump produced an empty file');
        }
    }

    protected function createZipBackup(string $zipPath, string $sqlPath): void
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create ZIP file');
        }

        // Add SQL dump
        if (file_exists($sqlPath)) {
            $zip->addFile($sqlPath, 'database.sql');
        }

        // Add .env file
        $envPath = base_path('.env');
        if (file_exists($envPath)) {
            $zip->addFile($envPath, '.env');
        }

        // Add uploaded files from storage
        $uploadDirs = ['app/public', 'app/logo', 'app/item'];
        foreach ($uploadDirs as $dir) {
            $fullPath = storage_path($dir);
            if (is_dir($fullPath)) {
                $this->addDirectoryToZip($zip, $fullPath, 'storage/' . $dir);
            }
        }

        $zip->close();
    }

    protected function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipDir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipDir . '/' . substr($filePath, strlen($dirPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    public function downloadBackup(string $filename): mixed
    {
        $filename = basename($filename); // Prevent path traversal
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists($path)) {
            $this->alert('error', __('modules.backup.fileNotFound'));
            return null;
        }

        return response()->download($path);
    }

    public function deleteBackup(string $filename): void
    {
        $filename = basename($filename); // Prevent path traversal
        $path = storage_path('app/backups/' . $filename);

        if (file_exists($path)) {
            unlink($path);
            $this->loadBackups();

            $this->alert('success', __('modules.backup.backupDeleted'), [
                'toast' => true,
                'position' => 'top-end',
            ]);
        }
    }

    protected function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function render()
    {
        return view('livewire.superadmin-settings.backup-settings');
    }
}
