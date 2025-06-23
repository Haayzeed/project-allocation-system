<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class LLMService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->apiKey = $config['api_key'] ?? '';
        $this->baseUrl = $config['base_url'] ?? '';
    }

    /**
     * Generate allocation recommendations using LLM.
     */
    abstract public function generateAllocationRecommendations(
        array $students,
        array $projects,
        array $supervisors
    ): array;

    /**
     * Send request to LLM API.
     */
    abstract protected function sendRequest(string $prompt, array $options = []): array;

    /**
     * Format data for LLM consumption.
     */
    protected function formatDataForLLM(array $students, array $projects, array $supervisors): array
    {
        return [
            'students' => $students,
            'projects' => $projects,
            'supervisors' => $supervisors,
        ];
    }

    /**
     * Parse LLM response into structured allocation recommendations.
     */
    protected function parseAllocationResponse(string $response): array
    {
        try {
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from LLM');
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Failed to parse LLM response', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Validate allocation recommendations.
     */
    protected function validateRecommendations(array $recommendations): bool
    {
        if (!isset($recommendations['allocations']) || !is_array($recommendations['allocations'])) {
            return false;
        }

        foreach ($recommendations['allocations'] as $allocation) {
            if (!isset($allocation['student_id']) || !isset($allocation['supervisor_id']) || !isset($allocation['project_id'])) {
                return false;
            }
        }

        return true;
    }
} 