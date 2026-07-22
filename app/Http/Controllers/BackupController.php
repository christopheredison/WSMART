<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    /**
     * Get database type from configuration
     */
    private function getDatabaseType()
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        return $dbConfig['driver'] ?? 'mysql';
    }

    /**
     * Generate backup command based on database type
     */
    private function generateBackupCommand($dbConfig, $backupPath)
    {
        $dbType = $this->getDatabaseType();
        
        if ($dbType === 'pgsql') {
            // PostgreSQL backup command
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            $dbDatabase = $dbConfig['database'];
            
            return "export PGPASSWORD=\"$dbPassword\" && pg_dump -F t -U $dbUser -h $dbHost -p $dbPort $dbDatabase > \"$backupPath\"";
        } else {
            // MySQL backup command
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            $dbDatabase = $dbConfig['database'];
            
            return "mysqldump -h $dbHost -P $dbPort -u $dbUser -p$dbPassword $dbDatabase > \"$backupPath\"";
        }
    }

    /**
     * Generate restore command based on database type
     */
    private function generateRestoreCommand($dbConfig, $extractFile, $targetDatabase)
    {
        $dbType = $this->getDatabaseType();
        
        if ($dbType === 'pgsql') {
            // PostgreSQL restore command
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            
            return "export PGPASSWORD=\"$dbPassword\" && pg_restore -F t -U $dbUser -h $dbHost -p $dbPort -d $targetDatabase \"$extractFile\"";
        } else {
            // MySQL restore command
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            
            return "mysql -h $dbHost -P $dbPort -u $dbUser -p$dbPassword $targetDatabase < \"$extractFile\"";
        }
    }

    /**
     * Create database command based on database type
     */
    private function generateCreateDatabaseCommand($dbConfig, $databaseName)
    {
        $dbType = $this->getDatabaseType();
        
        if ($dbType === 'pgsql') {
            // PostgreSQL create database
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            
            return "export PGPASSWORD=\"$dbPassword\" && createdb -h $dbHost -p $dbPort -U $dbUser $databaseName";
        } else {
            // MySQL create database
            $dbHost = $dbConfig['host'];
            $dbPort = $dbConfig['port'];
            $dbUser = $dbConfig['username'];
            $dbPassword = $dbConfig['password'];
            
            return "mysql -h $dbHost -P $dbPort -u $dbUser -p$dbPassword -e \"CREATE DATABASE IF NOT EXISTS $databaseName\"";
        }
    }

    /**
     * Clean up orphaned temporary files
     */
    private function cleanupOrphanedTempFiles()
    {
        $tempDir = storage_path('backup/temp');
        $files = scandir($tempDir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            $filePath = $tempDir . '/' . $file;
            $fileTime = filemtime($filePath);
            
            // Remove files older than 1 hour (3600 seconds)
            if (time() - $fileTime > 3600) {
                unlink($filePath);
                Log::info("Cleaned up orphaned temp file: $file");
            }
        }
    }

    public function index(Request $request)
    {
        // if (session('login_backup') < time() - 30) {
        //     return view('backup.input_password');
        // }
        // session(['login_backup' => time()]);
        
        // Clean up orphaned temporary files
        
        $saveDir = storage_path('backup');
        if (!is_dir($saveDir)) {
            mkdir($saveDir);
        }

        $tempDir = storage_path('backup/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir);
        }

        $logDir = storage_path('backup/log');
        if (!is_dir($logDir)) {
            mkdir($logDir);
        }

        $this->cleanupOrphanedTempFiles();

        $backups = [];

        $backupLogs = scandir($logDir);
        $backupLogs = array_filter($backupLogs, function($item) {
            $exploded = explode('.', $item);
            $extension = $exploded[count($exploded) - 1] ?? '';
            return in_array($extension, ['log']) && !str_contains($item, '_restore.log');
        });

        $dbType = $this->getDatabaseType();
        array_walk($backupLogs, function($item) use ($logDir, &$backups, $dbType, $tempDir) {
            $backupName = str_replace('.' . ($dbType === 'pgsql' ? 'tar' : 'sql') . '.log', '', $item);
            $backupFileName = $backupName . ($dbType === 'pgsql' ? '.tar.zip' : '.sql.zip');
            $tempFileName = $backupName . ($dbType === 'pgsql' ? '.tar' : '.sql');

            $backup = [
                'name' => $backupName,
                'status' => 'in_progress',
                'backup_log' => is_file(storage_path('backup/log/' . $item)) ? file_get_contents(storage_path('backup/log/' . $item)) : '',
                'restore_log' => is_file(storage_path('backup/log/' . $backupFileName . '_restore.log')) ? file_get_contents(storage_path('backup/log/' . $backupFileName . '_restore.log')) : '',
                'filename' => $backupFileName,
            ];

            if (is_file(storage_path('backup/' . $backupFileName))) {
                $backup['status'] = 'success';
            } else if (is_file(storage_path($tempDir . '/' . $tempFileName))) {
                $backup['status'] = 'in_progress';
            }

            $backups[$backupFileName] = $backup;
        });

        return view('backup.index', compact('backups'));
    }

    // backup
    public function store(Request $request)
    {
        // if ($request->password) {
        //     if (password_verify($request->password, $request->user()->password)) {
        //         session(['login_backup' => time()]);
        //         return back();
        //     }
        //     return back()->withErrors('Password tidak tepat');
        // }
        // if (session('login_backup') < time() - 30) {
        //     return redirect(route('backups.index'))->withErrors('Silahkan konfirmasi kata sandi anda terlebih dahulu');
        // }
        // session(['login_backup' => time()]);
        if (!$request->zip_password) {
            return back()->withErrors('Backup Password wajib diisi');
        }
        $dbConfig = config('database.connections.' . config('database.default'));
        $dbType = $this->getDatabaseType();
        
        // Use .sql extension for MySQL, .tar for PostgreSQL
        $extension = ($dbType === 'pgsql') ? 'tar' : 'sql';
        $filename = ($request->backup_name ?: date('YmdHis').rand(1000,9999)).'.'.$extension;
        $backupPath = storage_path('backup/temp/' . $filename);
        $tempPath = storage_path('backup/temp/');
        $savePath = storage_path('backup/' . $filename);
        $logPath = storage_path('backup/log/' . $filename . '.log');
        $backupPassword = $request->zip_password;

        if (is_file($savePath . '.zip') || is_file($backupPath . '.zip')) {
            return back()->withErrors('File backup sudah ada');
        }

        $dirs = [storage_path('backup'), storage_path('backup/temp'), storage_path('backup/log')];
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        }

        $backupCommand = $this->generateBackupCommand($dbConfig, $backupPath);
        
        // Create a more robust script with error handling and cleanup
        $script = "
        (
            # Start backup process
            echo \"Starting backup process for $filename at $(date)\" >> \"$logPath\" 2>&1
            
            # Execute backup command
            $backupCommand
            
            # Check if backup was successful
            if [ \$? -eq 0 ] && [ -f \"$backupPath\" ]; then
                echo \"Backup completed successfully, creating zip archive...\" >> \"$logPath\" 2>&1
                
                # Create zip archive
                cd \"$tempPath\" && zip \"$filename.zip\" \"$filename\" -P \"$backupPassword\"
                
                if [ \$? -eq 0 ]; then
                    # Move zip to final location
                    mv \"$backupPath.zip\" \"$savePath.zip\"
                    
                    if [ \$? -eq 0 ]; then
                        # Clean up temporary backup file
                        rm -f \"$backupPath\"
                        echo \"Backup process completed successfully at $(date)\" >> \"$logPath\" 2>&1
                    else
                        echo \"ERROR: Failed to move zip file to final location\" >> \"$logPath\" 2>&1
                        # Clean up temporary files on failure
                        rm -f \"$backupPath\"
                        rm -f \"$backupPath.zip\"
                        exit 1
                    fi
                else
                    echo \"ERROR: Failed to create zip archive\" >> \"$logPath\" 2>&1
                    # Clean up temporary files on failure
                    rm -f \"$backupPath\"
                    exit 1
                fi
            else
                echo \"ERROR: Backup command failed or backup file not created\" >> \"$logPath\" 2>&1
                # Clean up any partial files
                rm -f \"$backupPath\"
                rm -f \"$backupPath.zip\"
                exit 1
            fi
        ) >> \"$logPath\" 2>&1 &
        ";
        
        exec($script, $output);
        Log::info('Backup '.$filename, $output);
        return back()->withSuccess('Proses backup berjalan');
    }

    public function show($filename)
    {
        // if (session('login_backup') < time() - 30) {
        //     return redirect(route('backups.index'))->withErrors('Silahkan konfirmasi kata sandi anda terlebih dahulu');
        // }
        // session(['login_backup' => time()]);
        $savePath = storage_path('backup/' . $filename);
        if (!is_file($savePath)) {
            abort(404);
        }
        $content = file_get_contents($savePath);
        return $response = response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$filename,
        ]);
    }

    public function destroy($filename)
    {
        // if (session('login_backup') < time() - 30) {
        //     return redirect(route('backups.index'))->withErrors('Silahkan konfirmasi kata sandi anda terlebih dahulu');
        // }
        // session(['login_backup' => time()]);
        $savePath = storage_path('backup/' . $filename);
        if (is_file($savePath)) {
            unlink($savePath);
        }
        $tempPath = storage_path('backup/temp/' . $filename);
        if (is_file($tempPath)) {
            unlink($tempPath);
        }
        $logPath = storage_path('backup/log/' . str_replace('.zip', '.log', $filename));
        if (is_file($logPath)) {
            unlink($logPath);
        }

        return [
            'status' => 'success',
            'message' => 'Berhasil menghapus backup',
        ];
    }

    // backup
    public function restore(Request $request)
    {
        // if (session('login_backup') < time() - 30) {
        //     return redirect(route('backups.index'))->withErrors('Silahkan konfirmasi kata sandi anda terlebih dahulu');
        // }
        // session(['login_backup' => time()]);
        $dbConfig = config('database.connections.' . config('database.default'));
        $dbType = $this->getDatabaseType();
        $dbHost = $dbConfig['host'];
        $dbPort = $dbConfig['port'];
        $dbUser = $dbConfig['username'];
        $dbPassword = $dbConfig['password'];
        $dbDatabase = $dbConfig['database'];
        $dbOldDatabase = $dbConfig['database'].date('YmdHi');

        $filename = $request->file('file') ? $request->file('file')->getClientOriginalName() : $request->filename;
        $savePath = storage_path('backup/' . $filename);
        $extractPath = storage_path('backup/temp/');
        $newFileName = explode('.', $filename);
        array_pop($newFileName);
        $newFileName = implode('.', $newFileName);
        $extractFile = storage_path('backup/temp/' . $newFileName);
        $logPath = storage_path('backup/log/' . $filename . '_restore.log');
        $artisan = base_path('artisan');
        $backupPassword = $request->zip_password;

        if ($file = $request->file('file')) {
            $request->validate([
                'file' => 'file|mimes:zip'
            ]);
            if (is_file($savePath)) {
                return back()->withErrors(["File $filename sudah ada. Ubah nama file atau restore melalui backup history di bawah"]);
            }
            file_put_contents($savePath, $file->get());
        }

        if (!is_file($savePath)) {
            abort(404);
        }

        // Extract backup file with error handling
        $extractScript = "
        (
            echo \"--------------------------------\" >> \"$logPath\" 2>&1
            echo \"Starting restore process for $filename at $(date)\" >> \"$logPath\" 2>&1
            echo \"Extracting backup file...\" >> \"$logPath\" 2>&1
            
            unzip -P \"$backupPassword\" \"$savePath\" -d \"$extractPath\"
            
            if [ \$? -eq 0 ] && [ -f \"$extractFile\" ]; then
                echo \"Extraction completed successfully\" >> \"$logPath\" 2>&1
                exit 0
            else
                echo \"ERROR: Failed to extract backup file or file not found\" >> \"$logPath\" 2>&1
                # Clean up any partial extraction
                rm -f \"$extractFile\"
                exit 1
            fi
        ) >> \"$logPath\" 2>&1
        ";
        
        exec($extractScript, $output, $extractReturnCode);
        
        if ($extractReturnCode !== 0 || !is_file($extractFile)) {
            if ($file = $request->file('file')) {
                return back()->withErrors(['File rusak atau password salah']);
            }
            return [
                'status' => 'fail',
                'message' => 'File rusak atau password yang dimasukkan salah.',
            ];
        }

        // Create backup database using appropriate command
        $createDbCommand = $this->generateCreateDatabaseCommand($dbConfig, $dbOldDatabase);
        exec($createDbCommand, $output);

        // Generate restore command based on database type
        $restoreCommand = $this->generateRestoreCommand($dbConfig, $extractFile, $dbOldDatabase);
        
        // Create robust restore script with error handling and cleanup
        $restoreScript = "
        (
            echo \"Starting database restore...\" >> \"$logPath\" 2>&1
            
            # Put application in maintenance mode
            php $artisan down
            
            # Execute restore command
            $restoreCommand
            
            if [ \$? -eq 0 ]; then
                echo \"Database restore completed successfully\" >> \"$logPath\" 2>&1
                
                # Complete the restore process
                php $artisan complete_restore $dbOldDatabase
                
                if [ \$? -eq 0 ]; then
                    echo \"Restore process completed successfully\" >> \"$logPath\" 2>&1
                    
                    # Clean up extracted file
                    rm -f \"$extractFile\"
                    
                    # Bring application back online
                    php $artisan up
                    
                    echo \"Restore process finished successfully at $(date)\" >> \"$logPath\" 2>&1
                else
                    echo \"ERROR: Failed to complete restore process\" >> \"$logPath\" 2>&1
                    # Clean up and bring application back online
                    rm -f \"$extractFile\"
                    php $artisan up
                    exit 1
                fi
            else
                echo \"ERROR: Database restore failed\" >> \"$logPath\" 2>&1
                # Clean up and bring application back online
                rm -f \"$extractFile\"
                php $artisan up
                exit 1
            fi
        ) >> \"$logPath\" 2>&1 &
        ";

        exec($restoreScript, $output);
        Log::info('Restore '.$filename, $output);
        if ($file = $request->file('file')) {
            return back()->withSuccess(['Proses restore berjalan. Aplikasi tidak akan dapat digunakan sampai proses restore selesai']);
        }
        return [
            'status' => 'success',
            'message' => 'Proses restore berjalan. Aplikasi tidak akan dapat digunakan sampai proses restore selesai',
        ];
    }
}
