<?php
header('Content-Type: application/json; charset=utf-8');

$response = [
    'success' => true,
    'message' => 'Test OK',
    'data' => [
        'id' => '123',
        'nombre' => 'Test User'
    ]
];

echo json_encode($response);
?>