<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class LLMFactory
{
    /**
     * Create an LLM service instance based on configuration.
     */
    public static function create(string $provider = null): LLMService
    {
        $provider = $provider ?? Config::get('llm.default_provider', 'gemini');
        $config = Config::get("llm.providers.{$provider}", []);

        return match ($provider) {
            'gemini' => new GeminiLLMService($config),
            'openai' => new OpenAILLMService($config),
            'anthropic' => new AnthropicLLMService($config),
            default => throw new \InvalidArgumentException("Unsupported LLM provider: {$provider}"),
        };
    }

    /**
     * Get available LLM providers.
     */
    public static function getAvailableProviders(): array
    {
        return [
            'gemini' => 'Google Gemini',
            'openai' => 'OpenAI GPT',
            'anthropic' => 'Anthropic Claude',
        ];
    }

    /**
     * Validate provider configuration.
     */
    public static function validateConfig(string $provider, array $config): bool
    {
        $requiredFields = match ($provider) {
            'gemini' => ['api_key'],
            'openai' => ['api_key'],
            'anthropic' => ['api_key'],
            default => throw new \InvalidArgumentException("Unsupported LLM provider: {$provider}"),
        };

        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                return false;
            }
        }

        return true;
    }
} 