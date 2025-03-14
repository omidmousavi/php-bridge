<?php

function bridge($endpoint)
{
    $queryString = $_SERVER['QUERY_STRING'];
    $laravelUrl = $endpoint . (!empty($queryString) ? '?' . $queryString : '');
    
    $method = $_SERVER['REQUEST_METHOD'];
    $headers = getallheaders();
    $body = file_get_contents("php://input");
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $laravelUrl);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // ✅ Get headers in response
    
    // Forward headers
    $curlHeaders = [];
    foreach ($headers as $key => $value) {
        $curlHeaders[] = "$key: $value";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Separate headers from body
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $responseHeaders = substr($response, 0, $headerSize);
    $responseBody = substr($response, $headerSize);
    
    // Close cURL
    curl_close($ch);
    
    // Forward response headers to user
    $headerLines = explode("\r\n", trim($responseHeaders));
    foreach ($headerLines as $headerLine) {
        if (!empty($headerLine) && !preg_match('/^Transfer-Encoding:|^Content-Length:/i', $headerLine)) {
            header($headerLine);
        }
    }
    
    // Return response from Laravel
    http_response_code($httpCode);
    return $responseBody;
}