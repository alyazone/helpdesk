<?php
/**
 * API - Get Active Officers
 * Returns list of officers with status 'bertugas'
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$db = getDB();

try {
    // Get all officers with status 'bertugas'
    $stmt = $db->prepare("SELECT id, nama, email, no_telefon, status FROM officers WHERE status = 'bertugas' ORDER BY nama ASC");
    $stmt->execute();
    $officers = $stmt->fetchAll();

    jsonResponse(true, 'Officers retrieved successfully', [
        'officers' => $officers,
        'count' => count($officers)
    ]);

} catch (Exception $e) {
    jsonResponse(false, 'Ralat: ' . $e->getMessage());
}
