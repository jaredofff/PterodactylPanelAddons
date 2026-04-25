<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Requests;

use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class ChangeVersionRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|string|in:paper,purpur,fabric,forge,vanilla',
            'version' => 'required|string',
        ];
    }
}
