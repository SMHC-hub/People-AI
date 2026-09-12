<?php

namespace App\Http\Controllers;

use App\Services\MLServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function __construct(
        protected MLServiceClient $mlService
    ) {}

    /**
     * Process a natural language query about HR data.
     */
    public function query(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'context' => ['sometimes', 'array'],
        ]);

        try {
            $response = $this->mlService->queryNLP([
                'question' => $validated['question'],
                'context' => $validated['context'] ?? [],
                'tenant_id' => $request->user()->tenant_id,
                'user_role' => $request->user()->role,
            ]);

            // Store in chat history
            $this->storeChatHistory($request->user()->id, $validated['question'], $response);

            return $this->success($response);
        } catch (\Exception $e) {
            return $this->error('Chatbot service unavailable: '.$e->getMessage(), 503);
        }
    }

    /**
     * Query HR policy information.
     */
    public function policy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'category' => ['sometimes', 'string', 'in:leave,attendance,benefits,general'],
        ]);

        try {
            $response = $this->mlService->chatPolicy([
                'question' => $validated['question'],
                'category' => $validated['category'] ?? 'general',
                'tenant_id' => $request->user()->tenant_id,
            ]);

            $content = $response['answer'] ?? $response['content'] ?? '';
            $citations = [];
            foreach (($response['sources'] ?? []) as $src) {
                $citations[] = [
                    'document_title' => $src['document'] ?? $src['title'] ?? 'Company Policy',
                    'source_type' => 'hr_policy',
                    'relevance_score' => (float) ($src['similarity'] ?? 0.88),
                    'content_snippet' => $src['text'] ?? $src['snippet'] ?? '',
                ];
            }

            $normalized = [
                'id' => 'msg-'.uniqid(),
                'answer' => $content,
                'content' => $content,
                'role' => 'assistant',
                'timestamp' => now()->toIso8601String(),
                'citations' => $citations,
                'sources' => $response['sources'] ?? [],
            ];

            return $this->success($normalized);
        } catch (\Exception $e) {
            return $this->error('Policy chatbot service unavailable: '.$e->getMessage(), 503);
        }
    }

    /**
     * Get chat history for the authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        $cacheKey = "chat_history:{$request->user()->id}";
        $history = Cache::get($cacheKey, []);

        // Return the most recent 50 messages
        $history = array_slice($history, -50);

        return $this->success($history);
    }

    /**
     * Store a chat message in history.
     */
    protected function storeChatHistory(int $userId, string $question, mixed $response): void
    {
        $cacheKey = "chat_history:{$userId}";
        $history = Cache::get($cacheKey, []);

        $history[] = [
            'question' => $question,
            'response' => $response,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 100 messages
        if (count($history) > 100) {
            $history = array_slice($history, -100);
        }

        Cache::put($cacheKey, $history, now()->addDays(30));
    }
}
