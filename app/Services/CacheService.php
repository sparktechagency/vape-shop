<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    // Cache Tags
    const TAG_USERS = 'users';
    const TAG_STORES = 'stores';
    const TAG_PRODUCTS = 'products';
    const TAG_SEARCH = 'search';
    const TAG_BRANDS = 'brands';
    const TAG_WHOLESALERS = 'wholesalers';
    const TAG_LOCATIONS = 'locations';
    const TAG_ROLES = 'roles';
    const TAG_SLIDERS = 'sliders';
    const TAG_HOME = 'home';
    const TAG_CATEGORIES = 'categories';
    const TAG_ADMIN = 'admin';
    const TAG_COUNTRIES = 'countries';

    /**
     * Clear cache by single tag
     */
    public static function clearByTag(string $tag): bool
    {
        try {
            Cache::tags([$tag])->flush();
            Log::info("Cache cleared for tag: {$tag}");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear cache for tag: {$tag}. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear cache by multiple tags
     */
    public static function clearByTags(array $tags): bool
    {
        try {
            foreach ($tags as $tag) {
                Cache::tags([$tag])->flush();
            }
            Log::info("Cache cleared for tags: " . implode(', ', $tags));
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear cache for tags: " . implode(', ', $tags) . ". Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all application cache
     */
    public static function clearAll(): bool
    {
        try {
            $allTags = [
                self::TAG_USERS,
                self::TAG_STORES,
                self::TAG_PRODUCTS,
                self::TAG_SEARCH,
                self::TAG_BRANDS,
                self::TAG_WHOLESALERS,
                self::TAG_LOCATIONS,
                self::TAG_ROLES,
                self::TAG_SLIDERS,
                self::TAG_HOME,
                self::TAG_CATEGORIES,
                self::TAG_ADMIN,
                self::TAG_COUNTRIES
            ];

            foreach ($allTags as $tag) {
                Cache::tags([$tag])->flush();
            }

            Log::info("All application cache cleared");
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to clear all cache. Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear user related cache
     */
    public static function clearUserCache(int $userId = null): bool
    {
        $tags = [
            self::TAG_USERS,
            self::TAG_SEARCH,
            self::TAG_STORES,
            self::TAG_LOCATIONS,
            self::TAG_PRODUCTS,
            self::TAG_ROLES
        ];

        return self::clearByTags($tags);
    }

    /**
     * Clear store related cache
     */
    public static function clearStoreCache(): bool
    {
        $tags = [
            self::TAG_STORES,
            self::TAG_LOCATIONS,
            self::TAG_SEARCH,
            self::TAG_USERS
        ];

        return self::clearByTags($tags);
    }

    /**
     * Clear product related cache
     */
    public static function clearProductCache(): bool
    {
        $tags = [
            self::TAG_PRODUCTS,
            self::TAG_SEARCH,
            self::TAG_USERS
        ];

        return self::clearByTags($tags);
    }

    /**
     * Clear category related cache
     */
    public static function clearCategoryCache(): bool
    {
        $tags = [
            self::TAG_CATEGORIES,
            self::TAG_HOME,
            self::TAG_PRODUCTS,
            self::TAG_ADMIN
        ];

        return self::clearByTags($tags);
    }

    /**
     * Clear slider related cache
     */
    public static function clearSliderCache(): bool
    {
        $tags = [
            self::TAG_SLIDERS,
            self::TAG_HOME
        ];

        return self::clearByTags($tags);
    }

    /**
     * Remember with tags helper
     */
    public static function rememberWithTags(string $key, array $tags, int $ttl, \Closure $callback)
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Put with tags helper
     */
    public static function putWithTags(string $key, $value, array $tags, int $ttl = null): bool
    {
        try {
            if ($ttl) {
                Cache::tags($tags)->put($key, $value, $ttl);
            } else {
                Cache::tags($tags)->put($key, $value);
            }
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to put cache with tags. Key: {$key}, Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get cache statistics (for debugging)
     */
    public static function getStats(): array
    {
        // This would depend on your Redis configuration
        // Basic implementation
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'redis_host' => config('database.redis.default.host'),
            'redis_port' => config('database.redis.default.port'),
        ];
    }
}
