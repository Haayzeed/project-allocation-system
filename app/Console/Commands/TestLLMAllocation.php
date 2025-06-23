<?php

namespace App\Console\Commands;

use App\Services\AllocationService;
use App\Services\LLMFactory;
use Illuminate\Console\Command;

class TestLLMAllocation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llm:test-allocation 
                            {provider? : The LLM provider to test (gemini, openai, anthropic)}
                            {--dry-run : Run without creating actual allocations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test LLM-based allocation functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = $this->argument('provider');
        $dryRun = $this->option('dry-run');

        $this->info('Testing LLM Allocation System');
        $this->info('=============================');

        // Check provider configuration
        if ($provider) {
            $this->info("Testing provider: {$provider}");
            
            if (!LLMFactory::validateConfig($provider, config("llm.providers.{$provider}"))) {
                $this->error("Provider '{$provider}' is not properly configured!");
                return 1;
            }
        } else {
            $provider = config('llm.default_provider');
            $this->info("Using default provider: {$provider}");
        }

        try {
            // Create LLM service
            $llmService = LLMFactory::create($provider);
            $this->info("✓ LLM service created successfully");

            // Create allocation service
            $allocationService = new AllocationService($llmService);
            $this->info("✓ Allocation service created successfully");

            // Check for submitted projects
            $submittedProjects = \App\Models\Project::where('status', 'submitted')
                ->whereDoesntHave('allocation')
                ->count();

            if ($submittedProjects === 0) {
                $this->warn("No submitted projects found for allocation.");
                $this->info("Creating test data...");
                $this->createTestData();
                $submittedProjects = \App\Models\Project::where('status', 'submitted')
                    ->whereDoesntHave('allocation')
                    ->count();
            }

            $this->info("Found {$submittedProjects} submitted projects");

            // Get statistics before allocation
            $beforeStats = $allocationService->getStatistics();
            $this->info("Before allocation:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Projects', $beforeStats['total_projects']],
                    ['Allocated Projects', $beforeStats['allocated_projects']],
                    ['Pending Allocations', $beforeStats['pending_allocations']],
                    ['Allocation Rate', round($beforeStats['allocation_rate'], 2) . '%'],
                ]
            );

            if ($dryRun) {
                $this->info("DRY RUN: Would generate allocations...");
                return 0;
            }

            // Generate allocations
            $this->info("Generating allocations...");
            $result = $allocationService->generateAllocations();

            // Display results
            $this->info("Allocation Results:");
            $this->info("==================");
            
            if (!empty($result['allocations'])) {
                $this->info("✓ Generated " . count($result['allocations']) . " allocations");
                
                if (isset($result['summary'])) {
                    $this->info("Summary:");
                    $this->table(
                        ['Metric', 'Value'],
                        [
                            ['Total Allocations', $result['summary']['total_allocations'] ?? 'N/A'],
                            ['Average Match Score', $result['summary']['average_match_score'] ?? 'N/A'],
                            ['Unallocated Students', $result['summary']['unallocated_students'] ?? 'N/A'],
                            ['Capacity Utilization', $result['summary']['capacity_utilization'] ?? 'N/A'],
                        ]
                    );
                }

                if (!empty($result['recommendations'])) {
                    $this->info("Recommendations:");
                    foreach ($result['recommendations'] as $recommendation) {
                        $this->line("• {$recommendation}");
                    }
                }
            }

            if (!empty($result['errors'])) {
                $this->error("Errors occurred:");
                foreach ($result['errors'] as $error) {
                    $this->error("• {$error}");
                }
            }

            // Get statistics after allocation
            $afterStats = $allocationService->getStatistics();
            $this->info("After allocation:");
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Projects', $afterStats['total_projects']],
                    ['Allocated Projects', $afterStats['allocated_projects']],
                    ['Pending Allocations', $afterStats['pending_allocations']],
                    ['Allocation Rate', round($afterStats['allocation_rate'], 2) . '%'],
                ]
            );

            $this->info("✓ LLM allocation test completed successfully!");

        } catch (\Exception $e) {
            $this->error("Test failed: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Create test data for allocation testing.
     */
    private function createTestData()
    {
        // Create test department if not exists
        $department = \App\Models\Department::firstOrCreate(
            ['code' => 'TEST'],
            [
                'name' => 'Test Department',
                'description' => 'Test department for LLM allocation testing',
            ]
        );

        // Create test specialization if not exists
        $specialization = \App\Models\Specialization::firstOrCreate(
            ['name' => 'Test Specialization'],
            ['description' => 'Test specialization for LLM allocation testing']
        );

        // Create test supervisor if not exists
        $supervisorUser = \App\Models\User::firstOrCreate(
            ['email' => 'test.supervisor@example.com'],
            [
                'name' => 'Test Supervisor',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
            ]
        );

        $supervisor = \App\Models\Supervisor::firstOrCreate(
            ['staff_id' => 'TEST001'],
            [
                'user_id' => $supervisorUser->id,
                'department_id' => $department->id,
                'title' => 'Dr.',
                'bio' => 'Test supervisor for LLM allocation testing',
                'max_students' => 5,
                'is_active' => true,
            ]
        );

        $supervisor->specializations()->sync([$specialization->id]);

        // Create test student if not exists
        $studentUser = \App\Models\User::firstOrCreate(
            ['email' => 'test.student@example.com'],
            [
                'name' => 'Test Student',
                'password' => bcrypt('password'),
                'role' => 'student',
            ]
        );

        $student = \App\Models\Student::firstOrCreate(
            ['student_id' => 'TEST001'],
            [
                'user_id' => $studentUser->id,
                'department_id' => $department->id,
                'level' => '400',
                'session' => '2023/2024',
            ]
        );

        // Create test project if not exists
        $project = \App\Models\Project::firstOrCreate(
            ['student_id' => $student->id],
            [
                'title' => 'Test Project for LLM Allocation',
                'description' => 'This is a test project to verify LLM allocation functionality',
                'objectives' => 'Test the AI allocation system',
                'methodology' => 'Experimental testing',
                'status' => 'submitted',
            ]
        );

        $project->specializations()->sync([$specialization->id]);

        $this->info("✓ Test data created successfully");
    }
} 