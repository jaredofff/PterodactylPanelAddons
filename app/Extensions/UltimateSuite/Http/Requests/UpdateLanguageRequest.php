<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Requests;

use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class UpdateLanguageRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'language' => 'required|string|in:en,es',
        ];
    }
}
