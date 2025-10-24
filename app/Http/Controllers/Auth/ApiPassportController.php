<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ApiPassportController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        $tokenRequest = Request::create('/oauth/token', 'POST', [
            'grant_type'    => 'password',
            'client_id'     => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'username'      => $request->email,
            'password'      => $request->password,
            'scope'         => '',
        ]);

        $response = app()->handle($tokenRequest);
        $authResponse = json_decode($response->getContent(), true);

        if (isset($authResponse['access_token'])) {
            return response()->json([
                'data' => [
                    'user' => [
                        'id'    => $user->id,
                        'name'  => $user->name,
                        'email' => $user->email,
                    ],
                    'token_type'    => $authResponse['token_type'],
                    'expires_in'    => $authResponse['expires_in'],
                    'access_token'  => $authResponse['access_token'],
                    'refresh_token' => $authResponse['refresh_token'],
                ],
                'message' => 'Login successful',
            ]);
        }

        return response()->json([
            'error' => $authResponse['error_description'] ?? 'Unable to issue token',
        ], 400);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);

        $tokenRequest = Request::create('/oauth/token', 'POST', [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id'     => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'scope'         => '',
        ]);

        $response = app()->handle($tokenRequest);
        $authResponse = json_decode($response->getContent(), true);

        if (isset($authResponse['access_token'])) {
            return response()->json([
                'data' => [
                    'token_type'    => $authResponse['token_type'],
                    'expires_in'    => $authResponse['expires_in'],
                    'access_token'  => $authResponse['access_token'],
                    'refresh_token' => $authResponse['refresh_token'],
                ],
                'message' => 'Token refreshed successfully',
            ]);
        }

        return response()->json([
            'error' => $authResponse['error_description'] ?? 'Unable to refresh token',
        ], 400);
    }
}
