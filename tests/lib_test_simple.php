<?php
// Simple test script to verify lamslesson module functionality
// Run with: php /data/moodle/web52/public/mod/lamslesson/tests/lib_test_simple.php

define('CLI_SCRIPT', true);
require_once('/data/moodle/web52/public/config.php');
require_once('/data/moodle/web52/public/mod/lamslesson/lib.php');

$passed = 0;
$failed = 0;

function test_assert($condition, $message) {
    global $passed, $failed;
    if ($condition) {
        echo "✅ PASS: $message\n";
        $passed++;
    } else {
        echo "❌ FAIL: $message\n";
        $failed++;
    }
}

echo "=== LAMS Lesson Module Unit Tests ===\n\n";

// Test 1: Check all required functions exist
echo "Test Group 1: Required Functions Exist\n";
test_assert(function_exists('lamslesson_add_instance'), 'lamslesson_add_instance() exists');
test_assert(function_exists('lamslesson_update_instance'), 'lamslesson_update_instance() exists');
test_assert(function_exists('lamslesson_delete_instance'), 'lamslesson_delete_instance() exists');
test_assert(function_exists('lamslesson_get_url'), 'lamslesson_get_url() exists');
test_assert(function_exists('lamslesson_get_lesson'), 'lamslesson_get_lesson() exists');
test_assert(function_exists('lamslesson_fill_lesson'), 'lamslesson_fill_lesson() exists');
test_assert(function_exists('lamslesson_get_sequences_rest'), 'lamslesson_get_sequences_rest() exists');
test_assert(function_exists('lamslesson_get_lams_outputs'), 'lamslesson_get_lams_outputs() exists');
test_assert(function_exists('lamslesson_http_call_post'), 'lamslesson_http_call_post() exists');
test_assert(function_exists('lamslesson_get_datetime'), 'lamslesson_get_datetime() exists');

// Test 2: Check function return types using Reflection
echo "\nTest Group 2: Function Return Types\n";
$functions_with_return_types = [
    'lamslesson_add_instance' => 'int',
    'lamslesson_update_instance' => 'bool',
    'lamslesson_delete_instance' => 'bool',
    'lamslesson_get_url' => 'string',
    'lamslesson_get_lesson' => 'int',
    'lamslesson_get_sequences_rest' => 'string',
    'lamslesson_get_lams_outputs' => '?float',  // Nullable float
    'lamslesson_get_datetime' => 'float',
];

foreach ($functions_with_return_types as $funcname => $expected_type) {
    if (function_exists($funcname)) {
        $func = new ReflectionFunction($funcname);
        $return_type = $func->getReturnType();
        $actual_type = $return_type ? $return_type->getName() : 'unknown';
        // For nullable types, Reflection might return just 'float' but allowsNull is true
        if ($return_type && $return_type->allowsNull() && strpos($expected_type, '?') === 0) {
            $actual_type = '?' . $actual_type;
        }
        test_assert($actual_type === $expected_type,
            "$funcname() returns $expected_type (actual: $actual_type)");
    }
}

// Test 3: Check declare(strict_types=1) is present
echo "\nTest Group 3: declare(strict_types=1) Check\n";
$files_to_check = [
    '/data/moodle/web52/public/mod/lamslesson/lib.php',
    '/data/moodle/web52/public/mod/lamslesson/view.php',
    '/data/moodle/web52/public/mod/lamslesson/mod_form.php',
    '/data/moodle/web52/public/mod/lamslesson/db/access.php',
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        test_assert(strpos($content, 'declare(strict_types=1)') !== false,
            basename($file) . ' has declare(strict_types=1)');
    }
}

// Test 4: Check type safety fixes
echo "\nTest Group 4: Type Safety Fixes\n";

// Check lamslesson_http_call_post uses http_build_query
$lib_content = file_get_contents('/data/moodle/web52/public/mod/lamslesson/lib.php');
test_assert(strpos($lib_content, 'http_build_query') !== false,
    'lamslesson_http_call_post() uses http_build_query');
test_assert(strpos($lib_content, 'download_file_content') === false,
    'lamslesson_http_call_post() does NOT use deprecated download_file_content');

// Check trim() calls have (string) cast
preg_match_all('/trim\([^)]+\)/', $lib_content, $matches);
if (!empty($matches[0])) {
    $has_string_cast = false;
    foreach ($matches[0] as $trim_call) {
        if (strpos($trim_call, '(string)') !== false) {
            $has_string_cast = true;
            break;
        }
    }
    test_assert($has_string_cast, 'trim() calls have (string) cast');
}

// Check urlencode() calls have (string) cast
preg_match_all('/urlencode\([^)]+\)/', $lib_content, $matches);
if (!empty($matches[0])) {
    $has_string_cast = false;
    foreach ($matches[0] as $urlencode_call) {
        if (strpos($urlencode_call, '(string)') !== false) {
            $has_string_cast = true;
            break;
        }
    }
    test_assert($has_string_cast, 'urlencode() calls have (string) cast');
}

// Test 5: Check xmlize() calls are guarded
echo "\nTest Group 5: xmlize() False Check\n";
// Check that all xmlize($xml) calls are preceded by if ($xml === false)
$lines = explode("\n", $lib_content);
$xmlize_guarded = true;
for ($i = 0; $i < count($lines); $i++) {
    if (strpos($lines[$i], 'xmlize($xml)') !== false) {
        // Check previous few lines for false check
        $guarded = false;
        for ($j = max(0, $i - 5); $j < $i; $j++) {
            if (strpos($lines[$j], '$xml === false') !== false) {
                $guarded = true;
                break;
            }
        }
        if (!$guarded) {
            $xmlize_guarded = false;
            echo "  WARNING: xmlize() call at line " . ($i+1) . " might not be guarded\n";
        }
    }
}
test_assert($xmlize_guarded, 'All xmlize($xml) calls are guarded with if ($xml === false)');

// Test 6: Check legacy capabilities removed
echo "\nTest Group 6: Legacy Capabilities Removed\n";
$access_content = file_get_contents('/data/moodle/web52/public/mod/lamslesson/db/access.php');
test_assert(strpos($access_content, "'legacy'") === false,
    'db/access.php does NOT contain legacy capability arrays');

// Test 7: Test lamslesson_get_url() function works
echo "\nTest Group 7: lamslesson_get_url() Functionality\n";
if (function_exists('lamslesson_get_url')) {
    $url = lamslesson_get_url('testuser', 'Test', 'User', 'test@test.com',
                             'en', 'US', 123, 2, 'Test Course', time(), 'learner');
    test_assert(is_string($url), 'lamslesson_get_url() returns a string');
    test_assert(strpos($url, 'https://') === 0 || strpos($url, 'http://') === 0,
        'lamslesson_get_url() returns a valid URL');
    echo "  Generated URL: " . substr($url, 0, 80) . "...\n";
}

// Test 8: Test lamslesson_get_datetime() returns float
echo "\nTest Group 8: lamslesson_get_datetime() Return Type\n";
if (function_exists('lamslesson_get_datetime')) {
    $datetime = lamslesson_get_datetime();
    test_assert(is_float($datetime), 'lamslesson_get_datetime() returns float');
    test_assert($datetime > 1000000000000, 'lamslesson_get_datetime() returns valid timestamp');
}

// Summary
echo "\n" . str_repeat('=', 50) . "\n";
echo "TEST SUMMARY\n";
echo str_repeat('=', 50) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo str_repeat('=', 50) . "\n";

if ($failed == 0) {
    echo "🎉 ALL TESTS PASSED!\n";
    exit(0);
} else {
    echo "❌ SOME TESTS FAILED!\n";
    exit(1);
}
