<?php
/**
 * File Upload Handler
 * Handles standalone file uploads (AJAX)
 */

require_once __DIR__ . '/../config/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method');
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    jsonResponse(false, 'Tiada fail yang dimuat naik');
}

$file = $_FILES['file'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'Fail melebihi had saiz yang dibenarkan',
        UPLOAD_ERR_FORM_SIZE => 'Fail melebihi had saiz yang dibenarkan',
        UPLOAD_ERR_PARTIAL => 'Fail dimuat naik sebahagian sahaja',
        UPLOAD_ERR_NO_FILE => 'Tiada fail yang dimuat naik',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder sementara hilang',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis fail ke disk',
        UPLOAD_ERR_EXTENSION => 'Sambungan PHP menghentikan muat naik fail',
    ];
    $error_message = $error_messages[$file['error']] ?? 'Ralat tidak diketahui semasa muat naik';
    jsonResponse(false, $error_message);
}

$file_name = $file['name'];
$file_tmp = $file['tmp_name'];
$file_size = $file['size'];
$file_type = $file['type'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

// Validate file size
if ($file_size > MAX_FILE_SIZE) {
    jsonResponse(false, 'Saiz fail melebihi had maksimum ' . formatFileSize(MAX_FILE_SIZE));
}

// Validate file type
if (!in_array($file_type, ALLOWED_FILE_TYPES)) {
    jsonResponse(false, 'Jenis fail tidak dibenarkan. Hanya JPG, PNG, PDF, DOC, DOCX dibenarkan');
}

// Validate file extension
if (!in_array($file_ext, ALLOWED_FILE_EXTENSIONS)) {
    jsonResponse(false, 'Sambungan fail tidak dibenarkan');
}

// Generate unique filename
$new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
$upload_path = UPLOAD_DIR . $new_file_name;

// Create upload directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file_tmp, $upload_path)) {
    jsonResponse(true, 'Fail berjaya dimuat naik', [
        'file_name' => $new_file_name,
        'original_name' => $file_name,
        'file_size' => $file_size,
        'file_type' => $file_type,
        'file_path' => $upload_path
    ]);
} else {
    jsonResponse(false, 'Gagal memuat naik fail');
}
