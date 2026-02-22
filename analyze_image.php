<?php
include "config.php";

$apiKey = "YOUR_OPENAI_API_KEY";

$imagePath = $_FILES['image']['tmp_name'];
$imageData = base64_encode(file_get_contents($imagePath));

$data = [
    "model" => "gpt-4o-mini",
    "messages" => [
        [
            "role" => "user",
            "content" => [
                ["type" => "text", "text" => "Extract calories, protein, carbs, and fat from this nutrition label. Return JSON."],
                [
                    "type" => "image_url",
                    "image_url" => [
                        "url" => "data:image/jpeg;base64," . $imageData
                    ]
                ]
            ]
        ]
    ],
    "max_tokens" => 300
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $apiKey"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>