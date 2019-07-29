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
    "notification" => [
        "enabled" => env("VERISURE_NOTIFICATION_ENABLED", true),
        "channel" => env("VERISURE_NOTIFICATION_CHANNEL"),
    ],
    "status_job" => [
        "max_calls" => env("VERISURE_STATUS_JOBS_MAX_CALLS", 5),
        "sleep_between_calls" => env("VERISURE_STATUS_JOBS_SLEEP", 3),
    ],
    "session" => [
        "keep_alive" => env("VERISURE_SESSION_KEEP_ALIVE", false),
        // After this amount of time (in MINUTES) fram his creation, a token will be considered invalid
        // We enable this as Verisure starts raising suspicions after long active sessions
        "ttl" => env("VERISURE_SESSION_TTL", 240),
    ],
    "censure_responses" => env("VERISURE_CENSURE_RESPONSES", true),

    // TODO check if we can merge these 2
    "request_headers" => [
        "authenticity_token" => [
            "Connection" => "keep-alive",
            "Cache-Control" => "max-age=0",
            "Upgrade-Insecure-Requests" => "1",
            "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
            "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3",
            "Accept-Encoding" => "gzip, deflate, br",
            "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
        ],
        "generic_request" => [
            "Origin" => "https://customers.verisure.co.uk",
            "Connection" => "keep-alive",
            "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
            "Cache-Control" => "max-age=0",
            "Accept-Encoding" => "gzip, deflate, br",
            "Accept-Language" => "en-GB,en;q=0.9,it-IT;q=0.8,it;q=0.7,en-US;q=0.6",
            "User-Agent" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36",
            "Accept" => "application/json, text/javascript, */*; q=0.01",
            "Referer" => "https://customers.verisure.co.uk/gb/installations",
            "X-Requested-With" => "XMLHttpRequest",
        ],
    ],
];
