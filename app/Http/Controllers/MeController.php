<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class MeController extends BaseController
{
    /**
     * Get the authenticated user with its roles/permissions.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        return $this->sendResponse(
            new UserResource($request->user()),
            'User retrieved successfully.'
        );
    }
}
