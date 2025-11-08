<?php

// Define the log file path
$responseFile = '/home/bitrix/www/local/response.txt';

// Function to write messages to the log file
function logResponse($message)
{
    global $responseFile;
    file_put_contents($responseFile, $message . PHP_EOL, FILE_APPEND);
}

// Set a custom error handler
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    logResponse("[Error] $errstr in $errfile on line $errline");
});

// Set a custom exception handler
set_exception_handler(function ($exception) {
    logResponse("[Exception] " . $exception->getMessage());
});

// Enable all error notifications
error_reporting(E_ALL);

// Disable error output to the screen
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Include required classes
require_once 'BitrixAPI.php';
require_once 'DealHandler.php';

// Check if the required parameters are present in the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'activate' && isset($_POST['dealId'])) {
    $dealId = $_POST['dealId'];
    logResponse("Deal processing activated for ID: $dealId");

    // Set the webhook URL
    $webhookUrl = 'https://dd-familycrm.kz/rest/1/gveorm3pbcsyg539/';

    try {
        // Create class instances
        $bitrixAPI = new BitrixAPI($webhookUrl);
        $dealProcessor = new DealProcessor($bitrixAPI);
        logResponse("BitrixAPI and DealProcessor instances created");

        // Start processing the deal
        $result = $dealProcessor->processDeal($dealId);
        logResponse("Processing completed: " . print_r($result, true));

        // Return the processing result as JSON
        echo json_encode(['status' => 'deal processed', 'dealId' => $dealId, 'result' => $result]);
    } catch (Exception $e) {
        logResponse("[Exception] Deal processing error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Internal Server Error', 'message' => $e->getMessage()]);
    }
    exit;
} else {
    logResponse("Error: Missing 'action' or 'dealId' parameters");
    echo json_encode(['error' => 'Required parameters missing']);
}
