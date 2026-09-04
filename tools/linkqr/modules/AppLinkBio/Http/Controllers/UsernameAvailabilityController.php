<?php

namespace Modules\AppLinkBio\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AppLinkBio\Support\UsernameAvailability;

class UsernameAvailabilityController extends Controller
{
    public function __invoke(Request $request, UsernameAvailability $availability): JsonResponse
    {
        return response()->json(
            $availability->check((string) $request->query('username', ''))
        );
    }
}
