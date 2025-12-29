<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    /**
     * Display the admin settings page
     */
    public function index()
    {
        // Ensure settings table exists and is seeded
        $this->ensureSettingsTable();
        
        return view('admin.settings');
    }

    /**
     * Ensure settings table exists and is populated
     */
    private function ensureSettingsTable()
    {
        try {
            if (!Schema::hasTable('settings')) {
                // Run migration
                Artisan::call('migrate', ['--path' => 'database/migrations', '--force' => true]);
                
                // Run seeder
                Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
                
                Log::info('Settings table created and seeded automatically');
            } elseif (Setting::count() === 0) {
                // Table exists but no data, run seeder
                Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);
                
                Log::info('Settings table seeded automatically');
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure settings table: ' . $e->getMessage());
        }
    }

    /**
     * Load current settings
     */
    public function load()
    {
        try {
            $this->ensureSettingsTable();
            
            $settings = [
                'general' => Setting::getByGroup('general'),
                'email' => Setting::getByGroup('email'),
                'security' => Setting::getByGroup('security'),
                'payment' => Setting::getByGroup('payment'),
                'notifications' => Setting::getByGroup('notifications'),
                'integrations' => Setting::getByGroup('integrations'),
            ];

            return response()->json(['success' => true, 'settings' => $settings]);
        } catch (\Exception $e) {
            Log::error('Failed to load settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to load settings'], 500);
        }
    }

    /**
     * Save settings
     */
    public function save(Request $request)
    {
        try {
            $this->ensureSettingsTable();
            
            $settings = $request->input('settings', []);
            
            foreach ($settings as $group => $groupSettings) {
                foreach ($groupSettings as $key => $value) {
                    // Determine type based on value
                    $type = 'string';
                    if (is_bool($value)) {
                        $type = 'boolean';
                    } elseif (is_int($value)) {
                        $type = 'integer';
                    } elseif (is_float($value)) {
                        $type = 'float';
                    } elseif (is_array($value) || is_object($value)) {
                        $type = 'json';
                    }
                    
                    Setting::set($key, $value, $type, $group);
                }
            }
            
            Log::info('Settings saved', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to save settings: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save settings'], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache($type)
    {
        try {
            switch ($type) {
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'application':
                    Cache::flush();
                    break;
                case 'all':
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Cache::flush();
                    break;
                default:
                    return response()->json(['success' => false, 'message' => 'Invalid cache type'], 400);
            }

            Log::info("Cache cleared: {$type}", ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => ucfirst($type) . ' cache cleared successfully']);
        } catch (\Exception $e) {
            Log::error("Failed to clear {$type} cache: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => "Failed to clear {$type} cache"], 500);
        }
    }

    /**
     * Optimize database
     */
    public function optimizeDatabase()
    {
        try {
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                DB::statement("OPTIMIZE TABLE `{$tableName}`");
            }

            Log::info('Database optimized', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Database optimized successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to optimize database: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to optimize database'], 500);
        }
    }

    /**
     * Backup database
     */
    public function backupDatabase()
    {
        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = storage_path('app/backups');
            
            // Create backups directory if it doesn't exist
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }
            
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $filename;
            
            // Get database configuration
            $host = config('database.connections.mysql.host');
            $username = config('database.connections.mysql.username');
            $password = config('database.connections.mysql.password');
            $database = config('database.connections.mysql.database');
            $port = config('database.connections.mysql.port', 3306);
            
            // Try different backup methods
            $success = false;
            $output = [];
            $returnCode = 0;
            
            // Method 1: Try mysqldump with full path
            $mysqldumpPaths = [
                'mysqldump', // System PATH
                'C:\\xampp\\mysql\\bin\\mysqldump.exe', // XAMPP
                'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\mysqldump.exe', // WAMP
                'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe', // Laragon
            ];
            
            foreach ($mysqldumpPaths as $mysqldumpPath) {
                if ($success) break;
                
                $command = sprintf(
                    '"%s" -h%s -P%s -u%s -p%s %s > "%s" 2>&1',
                    $mysqldumpPath,
                    $host,
                    $port,
                    $username,
                    $password,
                    $database,
                    $filePath
                );
                
                exec($command, $output, $returnCode);
                
                if ($returnCode === 0 && file_exists($filePath) && filesize($filePath) > 0) {
                    $success = true;
                    break;
                }
            }
            
            // Method 2: PHP-based backup if mysqldump fails
            if (!$success) {
                $this->createPhpBackup($filePath);
                $success = file_exists($filePath) && filesize($filePath) > 0;
            }
            
            if ($success) {
                Log::info('Database backup created', ['filename' => $filename, 'user' => auth()->user()->email]);
                return response()->download($filePath, $filename)->deleteFileAfterSend(true);
            } else {
                throw new \Exception('All backup methods failed. Output: ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            Log::error('Failed to backup database: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to backup database: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Create PHP-based database backup
     */
    private function createPhpBackup($filePath)
    {
        try {
            $sql = "-- Database Backup Created: " . date('Y-m-d H:i:s') . "\n\n";
            
            // Get all tables
            $tables = DB::select('SHOW TABLES');
            $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
            
            foreach ($tables as $table) {
                $tableName = $table->$tableColumn;
                
                // Get table structure
                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $sql .= "-- Table structure for `{$tableName}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sql .= $createTable[0]->{'Create Table'} . ";\n\n";
                
                // Get table data
                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sql .= "-- Data for table `{$tableName}`\n";
                    $sql .= "INSERT INTO `{$tableName}` VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowData = [];
                        foreach ((array)$row as $value) {
                            if (is_null($value)) {
                                $rowData[] = 'NULL';
                            } else {
                                $rowData[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = '(' . implode(',', $rowData) . ')';
                    }
                    
                    $sql .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            file_put_contents($filePath, $sql);
        } catch (\Exception $e) {
            Log::error('PHP backup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get system information
     */
    public function systemInfo()
    {
        try {
            $info = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'database_version' => DB::select('SELECT VERSION() as version')[0]->version,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'disk_space' => [
                    'free' => disk_free_space('/'),
                    'total' => disk_total_space('/'),
                ],
            ];

            return response()->json(['success' => true, 'info' => $info]);
        } catch (\Exception $e) {
            Log::error('Failed to get system info: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get system information'], 500);
        }
    }

    /**
     * Enable maintenance mode
     */
    public function enableMaintenanceMode(Request $request)
    {
        try {
            $message = $request->input('message', 'Site is under maintenance. Please check back later.');
            
            Setting::enableMaintenanceMode($message);
            
            Log::info('Maintenance mode enabled', ['user' => auth()->user()->email, 'message' => $message]);
            
            return response()->json(['success' => true, 'message' => 'Maintenance mode enabled']);
        } catch (\Exception $e) {
            Log::error('Failed to enable maintenance mode: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to enable maintenance mode'], 500);
        }
    }

    /**
     * Disable maintenance mode
     */
    public function disableMaintenanceMode()
    {
        try {
            Setting::disableMaintenanceMode();
            
            Log::info('Maintenance mode disabled', ['user' => auth()->user()->email]);
            
            return response()->json(['success' => true, 'message' => 'Maintenance mode disabled']);
        } catch (\Exception $e) {
            Log::error('Failed to disable maintenance mode: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to disable maintenance mode'], 500);
        }
    }

    /**
     * Get maintenance mode status
     */
    public function getMaintenanceStatus()
    {
        try {
            $isEnabled = Setting::isMaintenanceMode();
            $message = Setting::get('maintenance_message', 'Site is under maintenance. Please check back later.');
            
            return response()->json([
                'success' => true,
                'maintenance_mode' => $isEnabled,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get maintenance status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to get maintenance status'], 500);
        }
    }
}