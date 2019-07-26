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
    "keep_session_alive" => env("VERISURE_KEEP_SESSION_ALIVE", false),
    "censure_responses" => env("VERISURE_CENSURE_RESPONSES", true),
];
