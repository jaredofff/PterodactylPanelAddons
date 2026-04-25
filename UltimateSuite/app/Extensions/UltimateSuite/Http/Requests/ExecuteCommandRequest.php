<?php

namespace Pterodactyl\Extensions\UltimateSuite\Http\Requests;

use Pterodactyl\Http\Requests\Api\Client\ClientApiRequest;

class ExecuteCommandRequest extends ClientApiRequest
{
    public function rules(): array
    {
        return [
            'command' => 'required|string',
            'player' => 'required|string',
        ];
    }
}
