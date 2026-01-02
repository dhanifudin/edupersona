<?php

use App\Services\GeminiAiService;

test('generateContent returns null when API key is not configured', function () {
    config(['gemini.api_key' => null]);

    $service = new GeminiAiService;
    $result = $service->generateContent('Test prompt');

    expect($result)->toBeNull();
});

test('service uses correct model from config', function () {
    config(['app.gemini_model' => 'gemini-2.0-flash']);

    $service = new GeminiAiService;

    // Use reflection to check the model is set correctly
    $reflection = new ReflectionClass($service);
    $property = $reflection->getProperty('textModel');
    $property->setAccessible(true);

    expect($property->getValue($service))->toBe('gemini-2.0-flash');
});

test('service uses default model when not configured', function () {
    config(['app.gemini_model' => null]);

    $service = new GeminiAiService;

    $reflection = new ReflectionClass($service);
    $property = $reflection->getProperty('textModel');
    $property->setAccessible(true);

    expect($property->getValue($service))->toBe('gemini-1.5-flash');
});

test('parseJsonResponse extracts JSON from markdown code blocks', function () {
    $service = new GeminiAiService;

    // Use reflection to access private method
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('parseJsonResponse');
    $method->setAccessible(true);

    $jsonInCodeBlock = "```json\n{\"test\": \"value\"}\n```";
    $result = $method->invoke($service, $jsonInCodeBlock);

    expect($result)->toBeArray()
        ->and($result['test'])->toBe('value');
});

test('parseJsonResponse handles plain JSON', function () {
    $service = new GeminiAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('parseJsonResponse');
    $method->setAccessible(true);

    $plainJson = '{"recommendations": [{"material_id": 1}]}';
    $result = $method->invoke($service, $plainJson);

    expect($result)->toBeArray()
        ->and($result['recommendations'])->toHaveCount(1);
});

test('parseJsonResponse returns null for invalid JSON', function () {
    $service = new GeminiAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('parseJsonResponse');
    $method->setAccessible(true);

    $invalidJson = 'not valid json';
    $result = $method->invoke($service, $invalidJson);

    expect($result)->toBeNull();
});

test('buildRecommendationPrompt includes learning profile data', function () {
    $service = new GeminiAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildRecommendationPrompt');
    $method->setAccessible(true);

    $profile = [
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
    ];

    $materials = [
        ['id' => 1, 'title' => 'Test Material'],
    ];

    $result = $method->invoke($service, $profile, $materials, 'Matematika');

    expect($result)
        ->toContain('Visual: 80%')
        ->toContain('Auditori: 60%')
        ->toContain('Kinestetik: 40%')
        ->toContain('visual')
        ->toContain('Matematika');
});

test('buildFeedbackPrompt includes all required data', function () {
    $service = new GeminiAiService;

    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('buildFeedbackPrompt');
    $method->setAccessible(true);

    $profile = [
        'visual_score' => 80,
        'auditory_score' => 60,
        'kinesthetic_score' => 40,
        'dominant_style' => 'visual',
    ];

    $progress = ['total_activities' => 5];
    $activities = [['title' => 'Test Activity']];

    $result = $method->invoke($service, $profile, $progress, $activities);

    expect($result)
        ->toContain('visual')
        ->toContain('80%')
        ->toContain('Test Activity');
});
