<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Service\TokenService;
use Exception;
use Illuminate\Http\Request;

use function response;

class CreateToken extends Controller
{
    /**
     * @throws Exception
     */
    public function __invoke(Request $request)
    {
        $user = $request->user('user');

        $token = TokenService::create($user);

        return response()
            ->json($token)
            ->setStatusCode(201);
    }
}
