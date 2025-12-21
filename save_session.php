<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['email'])) {
    $_SESSION['firebase_user'] = [
        'email' => $data['email'],
        'name' => $data['name'] ?? $data['email']
    ];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'No email provided']);
}
?>
