<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default LLM Provider
    |--------------------------------------------------------------------------
    |
    | This value determines the default LLM provider to use for AI allocations.
    | Supported providers: 'gemini', 'openai', 'anthropic'
    |
    */
    'default_provider' => env('LLM_DEFAULT_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | LLM Providers Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the settings for each LLM provider.
    |
    */
    'providers' => [
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            'api_version' => env('GEMINI_API_VERSION', 'v1beta'),
            'temperature' => env('GEMINI_TEMPERATURE', 0.3),
            'max_tokens' => env('GEMINI_MAX_TOKENS', 8192),
        ],

        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'model' => env('OPENAI_MODEL', 'gpt-4'),
            'temperature' => env('OPENAI_TEMPERATURE', 0.3),
            'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
        ],

        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-sonnet-20240229'),
            'max_tokens' => env('ANTHROPIC_MAX_TOKENS', 4000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allocation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the allocation process.
    |
    */
    'allocation' => [
        'max_retries' => env('LLM_ALLOCATION_MAX_RETRIES', 3),
        'timeout' => env('LLM_ALLOCATION_TIMEOUT', 60),
        'batch_size' => env('LLM_ALLOCATION_BATCH_SIZE', 50),
        'fallback_to_rule_based' => env('LLM_FALLBACK_TO_RULE_BASED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Prompt Templates
    |--------------------------------------------------------------------------
    |
    | Customizable prompt templates for different allocation scenarios.
    |
    */
    'prompts' => [
        'allocation' => [
            'system' => 'You are an AI system designed to allocate students to supervisors for academic projects. Provide responses in valid JSON format only.',
            'user' => 'Analyze the following data and provide allocation recommendations for matching students with supervisors based on project specializations, supervisor expertise, and capacity constraints.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configure logging for LLM interactions.
    |
    */
    'logging' => [
        'enabled' => env('LLM_LOGGING_ENABLED', true),
        'level' => env('LLM_LOGGING_LEVEL', 'info'),
        'log_prompts' => env('LLM_LOG_PROMPTS', false),
        'log_responses' => env('LLM_LOG_RESPONSES', false),
    ],
]; 