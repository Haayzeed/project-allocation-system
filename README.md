# LLM Integration for Project Allocation System

This document describes the AI-powered allocation system that uses Large Language Models (LLMs) to intelligently match students with supervisors based on project specializations, supervisor expertise, and capacity constraints.

## 🚀 Features

### **Multi-LLM Support**
- **Google Gemini** (Default) - Fast and cost-effective
- **OpenAI GPT-4** - High-quality reasoning
- **Anthropic Claude** - Advanced analysis capabilities
- **Extensible Architecture** - Easy to add new LLM providers

### **Intelligent Allocation Algorithm**
- **Specialization Matching** - Matches project areas with supervisor expertise
- **Department Alignment** - Considers same-department assignments
- **Capacity Management** - Respects supervisor student limits
- **Load Balancing** - Distributes workload evenly
- **Fallback System** - Rule-based allocation if LLM fails

### **Comprehensive Data Processing**
- **Student Data** - Academic level, department, session
- **Project Data** - Title, description, objectives, specializations
- **Supervisor Data** - Expertise, capacity, current load, bio

## 🏗️ Architecture

### **Service Layer**
```
LLMService (Abstract)
├── GeminiLLMService
├── OpenAILLMService
└── AnthropicLLMService
```

### **Factory Pattern**
```php
LLMFactory::create('gemini') // Creates Gemini service
LLMFactory::create('openai')  // Creates OpenAI service
LLMFactory::create('anthropic') // Creates Anthropic service
```

### **Data Flow**
1. **Data Collection** - Gather students, projects, supervisors
2. **Data Preparation** - Format for LLM consumption
3. **LLM Processing** - Send to AI for analysis
4. **Response Parsing** - Extract allocation recommendations
5. **Validation** - Verify recommendations are valid
6. **Allocation Creation** - Create database records

## 📋 Configuration

### **Environment Variables**
```env
# Default LLM Provider
LLM_DEFAULT_PROVIDER=gemini

# Gemini Configuration
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-1.5-flash
GEMINI_TEMPERATURE=0.3
GEMINI_MAX_TOKENS=8192

# OpenAI Configuration
OPENAI_API_KEY=your_openai_api_key
OPENAI_MODEL=gpt-4
OPENAI_TEMPERATURE=0.3
OPENAI_MAX_TOKENS=4000

# Anthropic Configuration
ANTHROPIC_API_KEY=your_anthropic_api_key
ANTHROPIC_MODEL=claude-3-sonnet-20240229
ANTHROPIC_MAX_TOKENS=4000

# Allocation Settings
LLM_ALLOCATION_MAX_RETRIES=3
LLM_ALLOCATION_TIMEOUT=60
LLM_FALLBACK_TO_RULE_BASED=true
```

### **Configuration File**
```php
// config/llm.php
return [
    'default_provider' => env('LLM_DEFAULT_PROVIDER', 'gemini'),
    'providers' => [
        'gemini' => [
            'api_key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
            // ... more config
        ],
        // ... other providers
    ],
];
```

## 🔧 Usage

### **Basic Allocation**
```php
use App\Services\AllocationService;
use App\Services\LLMFactory;

// Create LLM service
$llmService = LLMFactory::create('gemini');

// Create allocation service
$allocationService = new AllocationService($llmService);

// Generate allocations
$result = $allocationService->generateAllocations();
```

### **Admin Panel Integration**
```php
// In AIAllocationController
public function generateAllocations(Request $request)
{
    $provider = $request->input('provider', 'gemini');
    $llmService = LLMFactory::create($provider);
    $allocationService = new AllocationService($llmService);
    
    $result = $allocationService->generateAllocations();
    
    return response()->json($result);
}
```

### **Command Line Testing**
```bash
# Test with default provider
php artisan llm:test-allocation

# Test with specific provider
php artisan llm:test-allocation gemini

# Dry run (no actual allocations)
php artisan llm:test-allocation --dry-run
```

## 🤖 LLM Prompt Structure

### **Input Data Format**
```json
{
  "students": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "student_id": "STU001",
      "department": {
        "id": 1,
        "name": "Computer Science",
        "code": "CSC"
      },
      "level": "400",
      "session": "2023/2024"
    }
  ],
  "projects": [
    {
      "id": 1,
      "student_id": 1,
      "title": "AI-Powered Chatbot",
      "description": "Developing an intelligent chatbot...",
      "objectives": "Create a conversational AI...",
      "methodology": "Machine learning approach...",
      "specializations": [
        {
          "id": 1,
          "name": "Artificial Intelligence",
          "description": "AI and Machine Learning"
        }
      ]
    }
  ],
  "supervisors": [
    {
      "id": 1,
      "name": "Dr. Jane Smith",
      "email": "jane@example.com",
      "staff_id": "SUP001",
      "title": "Dr.",
      "bio": "Expert in AI and ML...",
      "department": {
        "id": 1,
        "name": "Computer Science",
        "code": "CSC"
      },
      "max_students": 5,
      "current_student_count": 2,
      "specializations": [
        {
          "id": 1,
          "name": "Artificial Intelligence",
          "description": "AI and Machine Learning"
        }
      ]
    }
  ]
}
```

### **Expected LLM Response**
```json
{
  "allocations": [
    {
      "student_id": 1,
      "supervisor_id": 1,
      "project_id": 1,
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
```

## 🔄 Adding New LLM Providers

### **1. Create Provider Class**
```php
<?php

namespace App\Services;

class CustomLLMService extends LLMService
{
    public function generateAllocationRecommendations(
        array $students,
        array $projects,
        array $supervisors
    ): array {
        // Implementation
    }

    protected function sendRequest(string $prompt, array $options = []): array {
        // API call implementation
    }
}
```

### **2. Update Factory**
```php
// In LLMFactory.php
public static function create(string $provider = null): LLMService
{
    return match ($provider) {
        'custom' => new CustomLLMService($config),
        // ... existing providers
    };
}
```

### **3. Add Configuration**
```php
// In config/llm.php
'providers' => [
    'custom' => [
        'api_key' => env('CUSTOM_API_KEY'),
        'base_url' => env('CUSTOM_BASE_URL'),
        // ... other config
    ],
],
```

## 🧪 Testing

### **Unit Tests**
```php
public function test_llm_allocation_generation()
{
    $llmService = Mockery::mock(LLMService::class);
    $llmService->shouldReceive('generateAllocationRecommendations')
        ->once()
        ->andReturn([
            'allocations' => [
                [
                    'student_id' => 1,
                    'supervisor_id' => 1,
                    'project_id' => 1,
                    'match_score' => 85.0,
                ]
            ]
        ]);

    $allocationService = new AllocationService($llmService);
    $result = $allocationService->generateAllocations();

    $this->assertNotEmpty($result['allocations']);
}
```

### **Integration Tests**
```bash
# Test with real API (requires API keys)
php artisan llm:test-allocation gemini
php artisan llm:test-allocation openai
php artisan llm:test-allocation anthropic
```

## 📊 Monitoring & Logging

### **Logging Configuration**
```php
// In config/llm.php
'logging' => [
    'enabled' => env('LLM_LOGGING_ENABLED', true),
    'level' => env('LLM_LOGGING_LEVEL', 'info'),
    'log_prompts' => env('LLM_LOG_PROMPTS', false),
    'log_responses' => env('LLM_LOG_RESPONSES', false),
],
```

### **Performance Metrics**
- **Response Time** - Time taken for LLM to respond
- **Success Rate** - Percentage of successful allocations
- **Match Quality** - Average match scores
- **Error Rate** - Failed allocation attempts

## 🔒 Security Considerations

### **API Key Management**
- Store API keys in environment variables
- Use different keys for different environments
- Rotate keys regularly
- Monitor API usage

### **Data Privacy**
- Sanitize data before sending to LLM
- Don't log sensitive information
- Use data retention policies
- Comply with GDPR/privacy regulations

### **Rate Limiting**
- Implement request throttling
- Monitor API quotas
- Handle rate limit errors gracefully
- Use fallback mechanisms

## 🚀 Performance Optimization

### **Caching**
```php
// Cache LLM responses for similar requests
$cacheKey = "llm_allocation_" . md5(json_encode($data));
$result = Cache::remember($cacheKey, 3600, function () use ($llmService, $data) {
    return $llmService->generateAllocationRecommendations($data);
});
```

### **Batch Processing**
```php
// Process allocations in batches
$batchSize = config('llm.allocation.batch_size', 50);
$batches = array_chunk($projects, $batchSize);

foreach ($batches as $batch) {
    $result = $allocationService->processBatch($batch);
}
```

### **Async Processing**
```php
// Use queues for large allocations
class ProcessAllocationJob implements ShouldQueue
{
    public function handle(AllocationService $service)
    {
        $service->generateAllocations();
    }
}
```

## 🔧 Troubleshooting

### **Common Issues**

1. **API Key Errors**
   ```bash
   # Check configuration
   php artisan config:show llm
   
   # Test connection
   php artisan llm:test-allocation --dry-run
   ```

2. **Invalid Response Format**
   ```php
   // Check LLM response parsing
   Log::debug('LLM Response', ['response' => $llmResponse]);
   ```

3. **Timeout Issues**
   ```php
   // Increase timeout
   'timeout' => env('LLM_ALLOCATION_TIMEOUT', 120),
   ```

### **Debug Commands**
```bash
# Check provider status
php artisan llm:status

# Test specific provider
php artisan llm:test-allocation gemini

# View logs
tail -f storage/logs/laravel.log | grep LLM
```

## 📈 Future Enhancements

1. **Advanced AI Models**
   - Fine-tuned models for allocation
   - Multi-modal analysis (documents, images)
   - Real-time learning from feedback

2. **Enhanced Features**
   - Preference learning
   - Conflict resolution
   - Dynamic reallocation
   - Performance analytics

3. **Integration**
   - Calendar integration
   - Email notifications
   - Mobile app support
   - API endpoints

## 📚 Resources

- [Google Gemini API Documentation](https://ai.google.dev/docs)
- [OpenAI API Documentation](https://platform.openai.com/docs)
- [Anthropic Claude API Documentation](https://docs.anthropic.com/)
- [Laravel HTTP Client](https://laravel.com/docs/http-client)
- [Laravel Queues](https://laravel.com/docs/queues)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.