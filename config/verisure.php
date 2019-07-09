<?php

return [
    "username" => env("VERISURE_APP_USERNAME"),
    "password" => env("VERISURE_APP_PASSWORD"),
    "url" => env("VERISURE_APP_URL"),
    "installation" => env("VERISURE_APP_INSTALLATION"),
    "auth" => [
        "active" => env("VERISURE_APP_AUTH_ACTIVE", false),
        "token" => env("VERISURE_APP_AUTH_TOKEN"),
    ],
    "notification_channel" => env("VERISURE_NOTIFICATION_CHANNEL")
];
