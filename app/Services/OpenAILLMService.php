<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAILLMService extends LLMService
{
    protected string $model = 'gpt-4';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->baseUrl = $config['base_url'] ?? 'https://api.openai.com/v1';
        $this->model = $config['model'] ?? 'gpt-4';
    }

    /**
     * Generate allocation recommendations using OpenAI.
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
            
            if (!isset($response['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response structure from OpenAI');
            }

            $llmResponse = $response['choices'][0]['message']['content'];
            $recommendations = $this->parseAllocationResponse($llmResponse);

            if (!$this->validateRecommendations($recommendations)) {
                throw new \Exception('Invalid allocation recommendations format');
            }

            return $recommendations;

        } catch (\Exception $e) {
            Log::error('OpenAI allocation generation failed', [
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
     * Send request to OpenAI API.
     */
    protected function sendRequest(string $prompt, array $options = []): array
    {
        $url = "{$this->baseUrl}/chat/completions";

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an AI system designed to allocate students to supervisors for academic projects. Provide responses in valid JSON format only.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => $options['temperature'] ?? 0.3,
            'max_tokens' => $options['max_tokens'] ?? 4000,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, $payload);

        if (!$response->successful()) {
            throw new \Exception("OpenAI API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the allocation prompt for OpenAI.
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