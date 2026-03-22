<?php

namespace App\Livewire\SuperadminSettings;

use App\Models\StorageSetting;
use Illuminate\Support\Facades\Storage;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupSettings extends Component
{
    use LivewireAlert, WithFileUploads;

    public $backups = [];
    public bool $isGenerating = false;
    public bool $isRestoring = false;
    public $restoreFile;

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

        $defaultDisk = config('filesystems.default');
        $isCloud = in_array($defaultDisk, StorageSetting::S3_COMPATIBLE_STORAGE);

        if ($isCloud) {
            // Download files from Minio/S3 and add to ZIP
            $this->addCloudFilesToZip($zip, $defaultDisk);
        } else {
            // Add local uploaded files
            $uploadDirs = ['app/public', 'app/logo', 'app/item'];
            foreach ($uploadDirs as $dir) {
                $fullPath = storage_path($dir);
                if (is_dir($fullPath)) {
                    $this->addDirectoryToZip($zip, $fullPath, 'storage/' . $dir);
                }
            }
        }

        $zip->close();
    }

    protected function addCloudFilesToZip(ZipArchive $zip, string $disk): void
    {
        $storage = Storage::disk($disk);
        $allFiles = $storage->allFiles('/');

        foreach ($allFiles as $file) {
            try {
                $contents = $storage->get($file);
                if ($contents !== null) {
                    $zip->addFromString('storage/' . $file, $contents);
                }
            } catch (\Exception $e) {
                // Skip files that can't be downloaded
                continue;
            }
        }
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

    public function restoreBackup(): void
    {
        $this->validate([
            'restoreFile' => 'required|file|mimes:zip|max:1048576',
        ]);

        $this->isRestoring = true;

        $extractDir = storage_path('app/backups/restore_' . now()->format('Y-m-d_H-i-s'));

        try {
            // 1. Extract ZIP
            $zip = new ZipArchive();
            $zipPath = $this->restoreFile->getRealPath();

            if ($zip->open($zipPath) !== true) {
                throw new \RuntimeException(__('modules.backup.invalidBackup'));
            }

            mkdir($extractDir, 0755, true);
            $zip->extractTo($extractDir);
            $zip->close();

            // 2. Validate ZIP contains database.sql
            $sqlPath = $extractDir . '/database.sql';
            if (!file_exists($sqlPath)) {
                throw new \RuntimeException(__('modules.backup.invalidBackup') . ': database.sql not found');
            }

            // 3. Import database
            $this->importDatabase($sqlPath);

            // 4. Restore storage files
            $storageDir = $extractDir . '/storage';
            if (is_dir($storageDir)) {
                $this->restoreStorageFiles($storageDir);
            }

            $this->alert('success', __('modules.backup.restoreSuccess'), [
                'toast' => false,
                'position' => 'center',
            ]);
        } catch (\Exception $e) {
            $this->alert('error', __('modules.backup.restoreFailed') . ': ' . $e->getMessage(), [
                'toast' => false,
                'position' => 'center',
                'showCancelButton' => true,
                'cancelButtonText' => __('app.close'),
            ]);
        } finally {
            // Clean up extracted files
            if (is_dir($extractDir)) {
                $this->deleteDirectory($extractDir);
            }

            $this->isRestoring = false;
            $this->restoreFile = null;
        }
    }

    protected function importDatabase(string $sqlPath): void
    {
        $config = config('database.connections.mysql');

        // Find mysql binary
        $mysql = 'mysql';
        $commonPaths = ['/usr/bin/mysql', '/usr/local/bin/mysql', '/usr/local/mysql/bin/mysql'];
        foreach ($commonPaths as $path) {
            if (file_exists($path)) {
                $mysql = $path;
                break;
            }
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? '3306';
        $user = $config['username'] ?? 'root';
        $pass = $config['password'] ?? '';
        $db   = $config['database'];

        $cmd = sprintf(
            '%s --host=%s --port=%s --user=%s %s %s < %s 2>&1',
            escapeshellarg($mysql),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($user),
            $pass !== '' ? '--password=' . escapeshellarg($pass) : '',
            escapeshellarg($db),
            escapeshellarg($sqlPath)
        );

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(600);
        $process->run();

        $error = $process->getErrorOutput() ?: $process->getOutput();
        $filteredError = trim(preg_replace('/.*Using a password on the command line interface can be insecure.*/i', '', $error));

        if (!$process->isSuccessful() && !empty($filteredError)) {
            throw new \RuntimeException('mysql import failed: ' . $filteredError);
        }
    }

    protected function restoreStorageFiles(string $storageDir): void
    {
        $defaultDisk = config('filesystems.default');
        $isCloud = in_array($defaultDisk, StorageSetting::S3_COMPATIBLE_STORAGE);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($storageDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();
            // Get path relative to the storage/ dir in the ZIP
            $relativePath = substr($filePath, strlen($storageDir) + 1);

            if ($isCloud) {
                Storage::disk($defaultDisk)->put($relativePath, file_get_contents($filePath));
            } else {
                $destPath = storage_path($relativePath);
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                copy($filePath, $destPath);
            }
        }
    }

    protected function deleteDirectory(string $dir): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getRealPath());
            } else {
                unlink($item->getRealPath());
            }
        }

        rmdir($dir);
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
