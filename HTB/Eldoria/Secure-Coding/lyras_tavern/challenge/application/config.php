<?php
$input = file_get_contents("php://stdin");
$input = trim($input);

$name = ($input !== "") ? $input : "Stranger";

$response = [
    "name" => $name,
    "year" => date("Y")
];

echo json_encode($response);
?>