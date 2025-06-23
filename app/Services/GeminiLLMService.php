<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLLMService extends LLMService
{
    protected string $model = 'gemini-1.5-flash';
    protected string $apiVersion = 'v1beta';

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->baseUrl = $config['base_url'] ?? 'https://generativelanguage.googleapis.com';
        $this->model = $config['model'] ?? 'gemini-1.5-flash';
    }

    /**
     * Generate allocation recommendations using Gemini.
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
            
            if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response structure from Gemini');
            }

            $llmResponse = $response['candidates'][0]['content']['parts'][0]['text'];
            $recommendations = $this->parseAllocationResponse($llmResponse);

            if (!$this->validateRecommendations($recommendations)) {
                throw new \Exception('Invalid allocation recommendations format');
            }

            return $recommendations;

        } catch (\Exception $e) {
            Log::error('Gemini allocation generation failed', [
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
     * Send request to Gemini API.
     */
    protected function sendRequest(string $prompt, array $options = []): array
    {
        $url = "{$this->baseUrl}/{$this->apiVersion}/models/{$this->model}:generateContent";

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.3,
                'topK' => $options['top_k'] ?? 40,
                'topP' => $options['top_p'] ?? 0.95,
                'maxOutputTokens' => $options['max_tokens'] ?? 8192,
            ],
            'safetySettings' => [
                [
                    'category' => 'HARM_CATEGORY_HARASSMENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_HATE_SPEECH',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ],
                [
                    'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                    'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url . "?key={$this->apiKey}", $payload);

        if (!$response->successful()) {
            throw new \Exception("Gemini API request failed: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Build the allocation prompt for Gemini.
     */
    private function buildAllocationPrompt(array $data): string
    {
        $studentsJson = json_encode($data['students'], JSON_PRETTY_PRINT);
        $projectsJson = json_encode($data['projects'], JSON_PRETTY_PRINT);
        $supervisorsJson = json_encode($data['supervisors'], JSON_PRETTY_PRINT);

        return <<<PROMPT
You are an AI system designed to allocate students to supervisors for academic projects. Your task is to match students with the most suitable supervisors based on project specializations, supervisor expertise, and capacity constraints.

Please analyze the following data and provide allocation recommendations:

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

Please provide your response in the following JSON format:
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

IMPORTANT: Respond ONLY with valid JSON. Do not include any explanatory text before or after the JSON response.
PROMPT;
    }
} 