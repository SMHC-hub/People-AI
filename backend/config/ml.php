<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ML Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the Python ML microservice that provides
    | turnover prediction, anomaly detection, leave forecasting,
    | and NLP/chatbot capabilities.
    |
    */

    'url' => env('ML_SERVICE_URL', 'http://localhost:8000'),

    'timeout' => env('ML_SERVICE_TIMEOUT', 30),

    'retry' => [
        'times' => env('ML_SERVICE_RETRY_TIMES', 3),
        'sleep' => env('ML_SERVICE_RETRY_SLEEP', 100),
    ],

    'endpoints' => [
        'predict' => [
            'turnover' => '/api/v1/predict/turnover',
            'leave' => '/api/v1/predict/leave',
            'risk_score' => '/api/v1/predict/risk-score',
        ],
        'analyze' => [
            'anomaly_detection' => '/api/v1/analyze/anomalies',
            'attendance_patterns' => '/api/v1/analyze/attendance-patterns',
            'department_analytics' => '/api/v1/analyze/department',
        ],
        'chat' => [
            'query' => '/api/v1/chat/query',
            'policy' => '/api/v1/chat/policy',
        ],
    ],

    'api_key' => env('ML_SERVICE_API_KEY'),

];
