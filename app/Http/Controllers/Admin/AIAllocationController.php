<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AllocationService;
use App\Services\LLMFactory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AIAllocationController extends Controller
{
    protected AllocationService $allocationService;

    public function __construct(AllocationService $allocationService)
    {
        $this->allocationService = $allocationService;
    }

    /**
     * Display the AI allocation dashboard.
     */
    public function index(): Response
    {
        $statistics = $this->allocationService->getStatistics();
        $availableProviders = LLMFactory::getAvailableProviders();
        $currentProvider = config('llm.default_provider');

        return Inertia::render('Admin/AIAllocation/Index', [
            'statistics' => $statistics,
            'availableProviders' => $availableProviders,
            'currentProvider' => $currentProvider,
        ]);
    }

    /**
     * Generate AI-based allocations for all submitted projects.
     */
    public function generateAllocations(Request $request)
    {
        $request->validate([
            'provider' => 'nullable|string|in:gemini,openai,anthropic',
            'force_regenerate' => 'boolean',
        ]);

        $provider = $request->input('provider');
        $forceRegenerate = $request->boolean('force_regenerate', false);

        try {
            // Create LLM service with specified provider
            $llmService = $provider ? LLMFactory::create($provider) : null;
            
            // Create allocation service with LLM service
            $allocationService = $llmService ? new AllocationService($llmService) : $this->allocationService;

            $result = $allocationService->generateAllocations();

            $message = "Generated " . count($result['allocations']) . " allocations using " . 
                      ($provider ?: config('llm.default_provider')) . ".";
            
            if (!empty($result['errors'])) {
                $message .= " " . count($result['errors']) . " errors occurred.";
            }

            return redirect()->route('admin.ai-allocation.index')
                ->with('success', $message)
                ->with('allocation_summary', $result['summary'] ?? [])
                ->with('recommendations', $result['recommendations'] ?? [])
                ->with('errors', $result['errors']);

        } catch (\Exception $e) {
            return redirect()->route('admin.ai-allocation.index')
                ->with('error', 'Failed to generate allocations: ' . $e->getMessage());
        }
    }

    /**
     * Test LLM connection and configuration.
     */
    public function testConnection(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:gemini,openai,anthropic',
        ]);

        $provider = $request->input('provider');

        try {
            $llmService = LLMFactory::create($provider);
            
            // Test with minimal data
            $testData = [
                'students' => [
                    [
                        'id' => 1,
                        'name' => 'Test Student',
                        'department' => ['name' => 'Computer Science'],
                    ]
                ],
                'projects' => [
                    [
                        'id' => 1,
                        'title' => 'Test Project',
                        'specializations' => [['name' => 'AI']],
                    ]
                ],
                'supervisors' => [
                    [
                        'id' => 1,
                        'name' => 'Test Supervisor',
                        'specializations' => [['name' => 'AI']],
                        'max_students' => 5,
                        'current_student_count' => 0,
                    ]
                ],
            ];

            $result = $llmService->generateAllocationRecommendations(
                $testData['students'],
                $testData['projects'],
                $testData['supervisors']
            );

            if (!empty($result['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Connection test failed: ' . implode(', ', $result['errors']),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully connected to {$provider} API",
                'sample_response' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Get allocation statistics as JSON.
     */
    public function statistics()
    {
        return response()->json($this->allocationService->getStatistics());
    }

    /**
     * Get LLM provider configuration status.
     */
    public function providerStatus()
    {
        $providers = LLMFactory::getAvailableProviders();
        $status = [];

        foreach ($providers as $key => $name) {
            $config = config("llm.providers.{$key}");
            $status[$key] = [
                'name' => $name,
                'configured' => !empty($config['api_key']),
                'has_base_url' => !empty($config['base_url']),
            ];
        }

        return response()->json($status);
    }
} 