<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CacheService;

class ClearAppCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-cache {--type=all : Cache type to clear (all|users|stores|products|sliders|categories|locations|search|trending|forum|posts|feed|followers|ads)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application cache by type or all - Production ready cache management';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');

        $this->info("Clearing cache for: {$type}");

        switch ($type) {
            case 'users':
                if (CacheService::clearUserCache()) {
                    $this->info('✅ User cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear user cache');
                    return 1;
                }
                break;

            case 'stores':
                if (CacheService::clearStoreCache()) {
                    $this->info('✅ Store cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear store cache');
                    return 1;
                }
                break;

            case 'products':
                if (CacheService::clearProductCache()) {
                    $this->info('✅ Product cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear product cache');
                    return 1;
                }
                break;

            case 'sliders':
                if (CacheService::clearSliderCache()) {
                    $this->info('✅ Slider cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear slider cache');
                    return 1;
                }
                break;

            case 'categories':
                if (CacheService::clearCategoryCache()) {
                    $this->info('✅ Category cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear category cache');
                    return 1;
                }
                break;

            case 'countries':
                if (CacheService::clearByTags([CacheService::TAG_COUNTRIES, CacheService::TAG_LOCATIONS])) {
                    $this->info('✅ Country cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear country cache');
                    return 1;
                }
                break;

            case 'search':
                if (CacheService::clearByTag(CacheService::TAG_SEARCH)) {
                    $this->info('✅ Search cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear search cache');
                    return 1;
                }
                break;

            case 'all':
            default:
                if (CacheService::clearAll()) {
                    $this->info('✅ All application cache cleared successfully!');
                } else {
                    $this->error('❌ Failed to clear all cache');
                    return 1;
                }
                break;
        }

        $this->newLine();
        $this->info('Cache clearing completed!');
        return 0;
    }
}
