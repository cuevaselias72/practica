<?php
    $url = "https://reqres.in/api/users/2";
    $data = [
        "job" => "zion resident",
        "email" => "hola@gmail.com"
    ];

    $ch = curl_init($url);
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");

    $response = curl_exec($ch);
    curl_close($ch);

    $decodedResponse = json_decode($response);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($decodedResponse, JSON_PRETTY_PRINT);
    
?>