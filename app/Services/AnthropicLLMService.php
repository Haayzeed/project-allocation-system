<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnthropicLLMService extends LLMService
{
    protected string $model = 'claude-3-sonnet-20240229';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->baseUrl = $config['base_url'] ?? 'https://api.anthropic.com/v1';
        $this->model = $config['model'] ?? 'claude-3-sonnet-20240229';
    }

    /**
     * Generate allocation recommendations using Anthropic Claude.
     */
    public function generateAllocationRecommendations(
        array $students,
        array $projects,
        array $supervisors
    ): array
    {
        try {
            $formattedData = $this->formatDataForLLM($students, $projects, $supervisors);
            $prompt = $this->buildAllocationPrompt($formattedData);
            
            $response = $this->sendRequest($prompt);
            
            if (!isset($response['content'][0]['text'])) {
                throw new \Exception('Invalid response structure from Anthropic');
            }

            $llmResponse = $response['content'][0]['text'];
            $recommendations = $this->parseAllocationResponse($llmResponse);

            if (!$this->validateRecommendations($recommendations)) {
                throw new \Exception('Invalid allocation recommendations format');
            }

            return $recommendations;

        } catch (\Exception $e) {
            Log::error('Anthropic allocation generation failed', [
                'error' => $e->getMessage(),
                'students_count' => count($students),
                'projects_count' => count($projects),
                'supervisors_count' => count($supervisors),
            ]);

            return [
                'allocations' => [],
                'errors' => [$e->getMessage()],
            ];
        }
    }

    /**
     * Send request to Anthropic API.
     */
    protected function sendRequest(string $prompt, array $options = []): array
    {
        $url = "{$this->baseUrl}/messages";

        $payload = [
            'model' => $this->model,
            'max_tokens' => $options['max_tokens'] ?? 4000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'system' => 'You are an AI system designed to allocate students to supervisors for academic projects. Provide responses in valid JSON format only.',
        ];

        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception("Anthropic API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the allocation prompt for Anthropic Claude.
     */
    private function buildAllocationPrompt(array $data): string
    {
        $studentsJson = json_encode($data['students'], JSON_PRETTY_PRINT);
        $projectsJson = json_encode($data['projects'], JSON_PRETTY_PRINT);
        $supervisorsJson = json_encode($data['supervisors'], JSON_PRETTY_PRINT);

        return <<<PROMPT
Analyze the following data and provide allocation recommendations for matching students with supervisors:

STUDENTS DATA:
{$studentsJson}

PROJECTS DATA:
{$projectsJson}

SUPERVISORS DATA:
{$supervisorsJson}

ALLOCATION RULES:
1. Each student can only be allocated to one supervisor
2. Supervisors have maximum capacity limits (max_students field)
3. Prioritize matching project specializations with supervisor specializations
4. Consider department alignment (bonus points for same department)
5. Distribute workload evenly among supervisors
6. Ensure all allocations are feasible and respect constraints

Provide your response in the following JSON format:
{
    "allocations": [
        {
            "student_id": 1,
            "supervisor_id": 2,
            "project_id": 3,
            "match_score": 85.5,
            "reasoning": "Strong match in AI specialization, same department, supervisor has capacity"
        }
    ],
    "summary": {
        "total_allocations": 10,
        "average_match_score": 82.3,
        "unallocated_students": 2,
        "capacity_utilization": "85%"
    },
    "recommendations": [
        "Consider adding more AI specialists to handle demand",
        "Some supervisors are underutilized and could take more students"
    ]
}
PROMPT;
    }
} 