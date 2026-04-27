<?php

namespace Pterodactyl\Http\Controllers\Api\Client\Servers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Server;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Services\PteroGPT\AIService;
use Pterodactyl\Services\PteroGPT\RateLimiter;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Http\Requests\Api\Client\Servers\PteroGPT\ChatRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PteroGPTController extends ClientApiController
{
    public function __construct(
        private AIService $aiService,
        private RateLimiter $rateLimiter,
    ) {
        parent::__construct();
    }

    public function config(): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->aiService->getConfig(),
        ]);
    }

    public function limits(Server $server): JsonResponse
    {
        return new JsonResponse([
            'data' => $this->rateLimiter->getRemaining($server->id),
        ]);
    }

    public function chat(ChatRequest $request, Server $server): JsonResponse|StreamedResponse
    {
        $isStream = $request->input('stream', false);

        $response = $this->aiService->chat(
            server: $server,
            userId: $request->user()->id,
            message: $request->input('message'),
            context: $request->input('context', []),
            conversationHistory: $request->input('conversation_history', []),
            model: $request->input('model'),
            ipAddress: $request->ip(),
            stream: $isStream
        );

        Activity::event('server:pterogpt.chat')
            ->property('message_length', strlen($request->input('message')))
            ->property('is_stream', $isStream)
            ->log();

        if ($isStream && is_callable($response)) {
            return new StreamedResponse($response, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        }

        return new JsonResponse(['data' => $response]);
    }
}