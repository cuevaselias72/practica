<?php
    $url = "https://jsonplaceholder.typicode.com/posts/1";

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $response = curl_exec($ch);
    curl_close($ch);

    $decodedResponse = json_decode($response);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($decodedResponse, JSON_PRETTY_PRINT);
?>