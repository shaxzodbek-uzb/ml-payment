<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function handle()
    {
        $token = request()->all();
        $authToken = new AuthToken();
        $authToken->token = $token;
        $authToken->save();
        
        return redirect('https://www.wix.com/installer/install')->with([
            'token' => $token,
            'appId' => 'cde3294b-e695-4500-b3f8-59b00fd6516c',
            'redirectUrl' => 'https://brainly.uz/api/auth/handle',
        ]);
    }
}