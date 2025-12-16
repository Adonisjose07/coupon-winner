<?php
require_once 'sendgrid_config.php';

function sendEmail($to, $subject, $templateFile, $data = [])
{
    // 1. Load Template
    $templatePath = __DIR__ . '/../templates/' . $templateFile;
    if (!file_exists($templatePath)) {
        return ["status" => "error", "message" => "Template not found: $templateFile"];
    }

    $html = file_get_contents($templatePath);

    // 2. Replacements
    foreach ($data as $key => $value) {
        $html = str_replace("{{" . $key . "}}", htmlspecialchars($value), $html);
    }

    // 3. Prepare SendGrid JSON Payload
    $postData = [
        "personalizations" => [
            [
                "to" => [
                    ["email" => $to]
                ],
                "subject" => $subject
            ]
        ],
        "from" => [
            "email" => SENDGRID_FROM_EMAIL,
            "name" => SENDGRID_FROM_NAME
        ],
        "content" => [
            [
                "type" => "text/html",
                "value" => $html
            ]
        ]
    ];

    // 4. Send via cURL to SendGrid API
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.sendgrid.com/v3/mail/send");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . SENDGRID_API_KEY,
        "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // SendGrid returns 202 Accepted on success
    if ($httpCode == 200 || $httpCode == 202) {
        return ["status" => "success"];
    } else {
        return ["status" => "error", "message" => "SendGrid API Error ($httpCode): " . $result];
    }
}
?>