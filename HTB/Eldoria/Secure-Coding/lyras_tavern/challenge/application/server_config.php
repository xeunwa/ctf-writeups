<?php
// Database configuration
define('DB_PATH', __DIR__ . '/instance/chronicle.db');

// Application settings
define('APP_NAME', 'Lyra\'s Journals');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

function fetch_data($data) {
    $settings = "/tmp/php_config/proper_config.ini";

    $postData = http_build_query([
        'PHPRC' => $settings,
        'data'  => $data
    ]);

    $context = stream_context_create([
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 30
        ]
    ]);

    $response = file_get_contents('http://127.0.0.1:3000/cgi-bin/app.cgi', false, $context);

    return $response;
}


function parse_json($json){
    try{
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE){
            throw new Exception('Invalid JSON:' . json_last_error_msg());
        }
        return $data;
    } catch (Exception $e){
        // return the error
        return $e->getMessage();
    }
}

function footer_forger($name){
    $data = fetch_data($name); // It is HTML encoded, decode it
    $data = html_entity_decode($data);
    $data = strip_tags($data);

    $data = parse_json($data);
    if (is_string($data)){
        return $data;
    }
    return "Greetings " . $data['name'] . ", we see you are visiting us from the distant future, " . $data['year'] . " AD.";
}