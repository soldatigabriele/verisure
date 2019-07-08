<?php

return [
    "username" => env("VERISURE_APP_USERNAME", ""),
    "password" => env("VERISURE_APP_PASSWORD", ""),
    "url" => "https://customers.verisure.co.uk",
    "installation" => env("VERISURE_APP_INSTALLATION", ""),
    "auth" => [
        "active" => env("VERISURE_APP_AUTH_ACTIVE", false),
        "token" => env("VERISURE_APP_AUTH_TOKEN", ""),
    ],
];
