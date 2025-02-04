<?php
header('Content-Type: application/json');

// Your OpenAI API key
$api_key = 'YOUR-API-KEY-HERE';

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if message exists
if (!isset($data['message'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No message provided'
    ]);
    exit;
}

$message = $data['message'];

// Prepare the request to OpenAI API
$ch = curl_init('https://api.openai.com/v1/chat/completions');

$headers = [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
];

$post_data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are a helpful assistant.'
        ],
        [
            'role' => 'user',
            'content' => $message
        ]
    ],
    'temperature' => 0.7,
    'max_tokens' => 150
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

try {
    $response = curl_exec($ch);
    
    if ($response === false) {
        throw new Exception(curl_error($ch));
    }
    
    $decoded_response = json_decode($response, true);
    
    if (isset($decoded_response['error'])) {
        throw new Exception($decoded_response['error']['message']);
    }
    
    if (isset($decoded_response['choices'][0]['message']['content'])) {
        echo json_encode([
            'success' => true,
            'response' => $decoded_response['choices'][0]['message']['content']
        ]);
    } else {
        throw new Exception('Invalid response format from OpenAI');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

curl_close($ch);
?>
