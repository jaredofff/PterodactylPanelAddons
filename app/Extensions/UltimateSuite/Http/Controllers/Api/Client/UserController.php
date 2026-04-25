<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController;
use Pterodactyl\Extensions\UltimateSuite\Http\Requests\UpdateLanguageRequest;

class UserController extends ClientApiController
{
    public function updateLanguage(UpdateLanguageRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->language = $request->input('language');
        $user->save();

        return new JsonResponse([
            'success' => true,
            'language' => $user->language
        ]);
    }
}
