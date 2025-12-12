<?php

namespace App\Console\Commands;

use App\Services\FlhsmvSoapService;
use App\Services\CaliforniaTvccService;
use App\Services\NevadaNtsaService;
use App\Services\CcsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Exception;

class TestAllStateApis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'states:test-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all state API connections (Florida, California, Nevada, CCS)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Testing All State API Connections...');
        $this->newLine();

        $results = [];

        // Test Florida FLHSMV
        $results['Florida'] = $this->testFlorida();
        $this->newLine();

        // Test California TVCC
        $results['California'] = $this->testCalifornia();
        $this->newLine();

        // Test Nevada NTSA
        $results['Nevada'] = $this->testNevada();
        $this->newLine();

        // Test CCS
        $results['CCS'] = $this->testCcs();
        $this->newLine();

        // Summary
        $this->displaySummary($results);
    }

    /**
     * Test Florida FLHSMV connection.
     */
    protected function testFlorida(): array
    {
        $this->info('🏖️  Testing Florida FLHSMV DICDS...');
        
        try {
            $service = new FlhsmvSoapService();
            $result = $service->testConnection();
            
            if ($result['success']) {
                $this->info('✅ Florida: SOAP connection successful');
                return ['status' => 'success', 'message' => 'SOAP connection working'];
            } else {
                $this->warn('⚠️  Florida: SOAP failed, fallback available');
                $this->line('   Error: ' . $result['error']);
                return ['status' => 'fallback', 'message' => 'Using fallback mode'];
            }
        } catch (Exception $e) {
            $this->error('❌ Florida: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test California TVCC connection.
     */
    protected function testCalifornia(): array
    {
        $this->info('🌴 Testing California TVCC...');
        
        $wsdlUrl = config('state-integrations.california.tvcc.url');
        
        if (!$wsdlUrl) {
            $this->error('❌ California: WSDL URL not configured');
            return ['status' => 'error', 'message' => 'WSDL URL not configured'];
        }

        try {
            // Test WSDL accessibility
            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            ]);

            $headers = @get_headers($wsdlUrl, 1, $context);
            
            if ($headers && strpos($headers[0], '200') !== false) {
                $this->info('✅ California: WSDL accessible');
                
                // Test password
                $password = $this->getTvccPassword();
                if ($password) {
                    $this->info('✅ California: Password configured');
                    return ['status' => 'success', 'message' => 'TVCC API ready'];
                } else {
                    $this->warn('⚠️  California: Password not set');
                    return ['status' => 'warning', 'message' => 'Password not set'];
                }
            } else {
                $this->error('❌ California: WSDL not accessible');
                return ['status' => 'error', 'message' => 'WSDL not accessible'];
            }
        } catch (Exception $e) {
            $this->error('❌ California: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test Nevada NTSA connection.
     */
    protected function testNevada(): array
    {
        $this->info('🎰 Testing Nevada NTSA...');
        
        $url = config('state-integrations.nevada.ntsa.url');
        
        if (!$url) {
            $this->error('❌ Nevada: URL not configured');
            return ['status' => 'error', 'message' => 'URL not configured'];
        }

        try {
            // Test HTTP connectivity
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful() || $response->status() === 405) {
                // 405 Method Not Allowed is expected for GET on POST endpoint
                $this->info('✅ Nevada: HTTP endpoint accessible');
                return ['status' => 'success', 'message' => 'NTSA endpoint accessible'];
            } else {
                $this->warn('⚠️  Nevada: Unexpected response (' . $response->status() . ')');
                return ['status' => 'warning', 'message' => 'Unexpected response: ' . $response->status()];
            }
        } catch (Exception $e) {
            $this->error('❌ Nevada: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Test CCS connection.
     */
    protected function testCcs(): array
    {
        $this->info('⚖️  Testing CCS (Court Compliance System)...');
        
        $url = config('state-integrations.ccs.url');
        
        if (!$url) {
            $this->error('❌ CCS: URL not configured');
            return ['status' => 'error', 'message' => 'URL not configured'];
        }

        try {
            // Test HTTP connectivity
            $response = Http::timeout(10)->get($url);
            
            if ($response->successful() || $response->status() === 405) {
                // 405 Method Not Allowed is expected for GET on POST endpoint
                $this->info('✅ CCS: HTTP endpoint accessible');
                return ['status' => 'success', 'message' => 'CCS endpoint accessible'];
            } else {
                $this->warn('⚠️  CCS: Unexpected response (' . $response->status() . ')');
                return ['status' => 'warning', 'message' => 'Unexpected response: ' . $response->status()];
            }
        } catch (Exception $e) {
            $this->error('❌ CCS: Connection failed');
            $this->line('   Error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Display summary of all tests.
     */
    protected function displaySummary(array $results): void
    {
        $this->info('📊 Summary of State API Tests:');
        $this->newLine();

        $successCount = 0;
        $totalCount = count($results);

        foreach ($results as $state => $result) {
            $icon = match($result['status']) {
                'success' => '✅',
                'warning' => '⚠️ ',
                'fallback' => '🔄',
                default => '❌'
            };

            $this->line("$icon $state: " . $result['message']);
            
            if ($result['status'] === 'success' || $result['status'] === 'fallback') {
                $successCount++;
            }
        }

        $this->newLine();
        $this->info("Operational APIs: $successCount/$totalCount");
        
        if ($successCount === $totalCount) {
            $this->info('🎉 All state APIs are operational!');
        } elseif ($successCount > 0) {
            $this->warn('⚠️  Some APIs need attention, but system can operate with fallbacks');
        } else {
            $this->error('❌ Multiple API issues detected - check configurations');
        }

        $this->newLine();
        $this->info('💡 Next steps:');
        $this->line('• Run individual tests: php artisan flhsmv:test, php artisan california:test');
        $this->line('• Check admin interface: /admin/state-transmissions');
        $this->line('• Review logs: storage/logs/laravel.log');
        $this->line('• Set TVCC password: php artisan tvcc:password "password"');
    }

    /**
     * Get TVCC password from database.
     */
    protected function getTvccPassword(): ?string
    {
        try {
            $passwordRecord = \DB::table('tvcc_passwords')->latest('updated_at')->first();
            return $passwordRecord ? $passwordRecord->password : null;
        } catch (Exception $e) {
            return null;
        }
    }
}