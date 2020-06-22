<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminAuthController extends Controller
{
    use IssueTokenTrait;


    public function __construct()
    {
        $this->client = \Laravel\Passport\Client::where('password_client', 1)->first();
    }
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->getRoles() === json_encode(['admin']))
        {
            $credentials['isAdmin'] = true;

            $response = $this->issueToken($credentials, 'password');
            return response()->json($response, 200);

        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

    }

}
