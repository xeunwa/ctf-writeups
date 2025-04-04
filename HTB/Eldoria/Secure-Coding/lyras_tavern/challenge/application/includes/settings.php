<?php


function fetch_data($data){
    $settings = "/etc/php/8.2/fpm/php.ini";

    // Send a POST request to /cgi-bin/app.cgi
    $ch = curl_init("http://127.0.0.1:3000/cgi-bin/app.cgi");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Set the POST data
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'settings' => $settings,
        'data' => $data
    ]));

    // Execute the request
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;

}

function parse_json($json){
    try{
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE){
            throw new Exception('Invalid JSON');
        }
        return $data;
    } catch (Exception $e){
        // return the error
        return $e->getMessage();
    }
}