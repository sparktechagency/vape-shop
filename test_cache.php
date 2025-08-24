<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Cache;

echo "🧪 Testing Cache System..." . PHP_EOL;
echo "=========================" . PHP_EOL;

// Test 1: Basic Cache
echo "1. Testing basic cache operations..." . PHP_EOL;
Cache::put('test_key', 'test_value', 60);
$value = Cache::get('test_key');
$basicTest = ($value === 'test_value') ? '✅ PASSED' : '❌ FAILED';
echo "   Basic cache: {$basicTest}" . PHP_EOL;

// Test 2: Cache Tags
echo "2. Testing cache tags..." . PHP_EOL;
Cache::tags(['test_tag'])->put('test_tagged_key', 'tagged_value', 60);
$taggedValue = Cache::tags(['test_tag'])->get('test_tagged_key');
$tagsTest = ($taggedValue === 'tagged_value') ? '✅ PASSED' : '❌ FAILED';
echo "   Cache tags: {$tagsTest}" . PHP_EOL;

// Test 3: Cache with Array Data
echo "3. Testing cache with complex data..." . PHP_EOL;
$complexData = [
    'id' => 1,
    'name' => 'Test Product',
    'price' => 99.99,
    'created_at' => now()
];
Cache::tags(['products', 'test'])->put('complex_test', $complexData, 60);
$retrievedData = Cache::tags(['products', 'test'])->get('complex_test');
$complexTest = (is_array($retrievedData) && $retrievedData['id'] === 1) ? '✅ PASSED' : '❌ FAILED';
echo "   Complex data: {$complexTest}" . PHP_EOL;

// Test 4: Cache Invalidation
echo "4. Testing cache invalidation..." . PHP_EOL;
Cache::tags(['invalidation_test'])->put('inv_test', 'before_clear', 60);
$beforeClear = Cache::tags(['invalidation_test'])->get('inv_test');
Cache::tags(['invalidation_test'])->flush();
$afterClear = Cache::tags(['invalidation_test'])->get('inv_test');
$invalidationTest = ($beforeClear === 'before_clear' && $afterClear === null) ? '✅ PASSED' : '❌ FAILED';
echo "   Cache invalidation: {$invalidationTest}" . PHP_EOL;

// Clean up
Cache::forget('test_key');
Cache::tags(['test_tag'])->flush();
Cache::tags(['products', 'test'])->flush();

echo PHP_EOL;
echo "🎉 Cache Testing Complete!" . PHP_EOL;

// Summary
$allTests = [$basicTest, $tagsTest, $complexTest, $invalidationTest];
$passed = count(array_filter($allTests, fn($test) => strpos($test, '✅') !== false));
$total = count($allTests);

echo "📊 Results: {$passed}/{$total} tests passed" . PHP_EOL;

if ($passed === $total) {
    echo "🚀 Cache system is ready for production!" . PHP_EOL;
} else {
    echo "⚠️  Some tests failed. Review before production deployment." . PHP_EOL;
}
