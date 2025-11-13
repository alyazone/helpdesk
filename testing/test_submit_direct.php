<?php
/**
 * Direct PHP Test for Submit Complaint
 * This bypasses JavaScript and tests the API directly
 */

echo "<h1>Direct Submit Complaint Test</h1>";
echo "<hr>";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'jenis' => 'aduan',
    'perkara' => 'Test Complaint from Direct PHP',
    'keterangan' => 'This is a test to see if the PHP API works directly',
    'nama' => 'Direct Test User',
    'emel' => 'directtest@jpbdselangor.gov.my',
    'jawatan' => 'Tester',
    'bahagian' => 'IT Department',
    'sambungan' => '9999',
    'tingkat' => '5',
    'jenisAset' => 'komputer',
    'noPendaftaran' => 'DIRECT-TEST-001',
    'tarikhKerosakan' => '2025-01-15',
    'perihalKerosakan' => 'Test hardware issue',
    'perihalKerosakanValue' => 'komputer_hang'
];

echo "<h2>Calling API with test data...</h2>";
echo "<p>Data being sent:</p>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<hr>";
echo "<h2>API Response:</h2>";
echo "<div style='border: 1px solid #ccc; padding: 10px; background: #f9f9f9;'>";

// Capture output
ob_start();

// Include the API file
try {
    include __DIR__ . '/api/submit_complaint.php';
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

$output = ob_get_clean();

echo $output;
echo "</div>";

echo "<hr>";
echo "<h2>Check Database</h2>";
echo "<p>After running this test, check your database:</p>";
echo "<pre>SELECT * FROM complaints WHERE email = 'directtest@jpbdselangor.gov.my' ORDER BY created_at DESC LIMIT 1;</pre>";

echo "<hr>";
echo "<h2>Check Debug Log</h2>";
if (file_exists(__DIR__ . '/debug.log')) {
    echo "<p>Last 20 lines of debug.log:</p>";
    echo "<pre style='background: #000; color: #0f0; padding: 10px;'>";
    $lines = file(__DIR__ . '/debug.log');
    $last_lines = array_slice($lines, -20);
    echo htmlspecialchars(implode('', $last_lines));
    echo "</pre>";
} else {
    echo "<p>No debug.log file found</p>";
}
?>
