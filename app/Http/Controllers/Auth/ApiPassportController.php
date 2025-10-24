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
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $user['token'] = $user->createToken('Token-name')->accessToken;
            return response()->json(['data' => $user]);
        } else {
            return response()->json(['errors' => 'Unauthorized'], 401);
        }
    }

    public function refresh($request)
    {
        $response = Http::asForm()->post(url('/oauth/token'), [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id'     => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'scope'         => '',
        ]);

        $authResponse = $response->json();

        if (! empty($authResponse)) {
            return [
                'token_type'    => $authResponse['token_type'],
                'expires_in'    => $authResponse['expires_in'],
                'token'         => $authResponse['access_token'],
                'refresh_token' => $authResponse['refresh_token'],
            ];
        }

        return [];
    }
}
