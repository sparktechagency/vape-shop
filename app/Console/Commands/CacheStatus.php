<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:status {--detailed : Show detailed cache information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check cache system status and statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $detailed = $this->option('detailed');

        $this->info('🔍 Cache System Status');
        $this->newLine();

        // Check cache driver
        $driver = config('cache.default');
        $this->info("Default Cache Driver: {$driver}");

        try {
            if ($driver === 'redis') {
                $this->checkRedisStatus();
            }
            
            $this->checkCacheConnectivity();
            
            if ($detailed) {
                $this->showDetailedInfo();
            }

        } catch (\Exception $e) {
            $this->error('❌ Cache system error: ' . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('✅ Cache system check completed');
        return 0;
    }

    /**
     * Check Redis status
     */
    private function checkRedisStatus(): void
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            $this->info('Redis Server: ✅ Connected');
            
            if (isset($info['redis_version'])) {
                $this->info("Redis Version: {$info['redis_version']}");
            }
            
            if (isset($info['used_memory_human'])) {
                $this->info("Memory Usage: {$info['used_memory_human']}");
            }

        } catch (\Exception $e) {
            $this->error('❌ Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Check cache connectivity
     */
    private function checkCacheConnectivity(): void
    {
        try {
            $testKey = 'cache_test_' . time();
            $testValue = 'test_value';
            
            // Test write
            Cache::put($testKey, $testValue, 60);
            
            // Test read
            $retrieved = Cache::get($testKey);
            
            if ($retrieved === $testValue) {
                $this->info('Cache Read/Write: ✅ Working');
            } else {
                $this->error('❌ Cache read/write test failed');
            }
            
            // Clean up
            Cache::forget($testKey);

        } catch (\Exception $e) {
            $this->error('❌ Cache connectivity test failed: ' . $e->getMessage());
        }
    }

    /**
     * Show detailed information
     */
    private function showDetailedInfo(): void
    {
        $this->newLine();
        $this->info('📊 Detailed Cache Information:');
        
        $tags = [
            'users', 'products', 'stores', 'search',
            'sliders', 'categories', 'locations', 'trending',
            'hearts', 'forum', 'groups', 'posts', 'feed',
            'followers', 'ads'
        ];

        $this->table(['Cache Tag', 'Status'], array_map(function ($tag) {
            try {
                // Try to access cache with this tag
                Cache::tags([$tag])->remember('test_' . $tag, 1, function () {
                    return 'test';
                });
                return [$tag, '✅ Available'];
            } catch (\Exception $e) {
                return [$tag, '❌ Error'];
            }
        }, $tags));
    }
}
