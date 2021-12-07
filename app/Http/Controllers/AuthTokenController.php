<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function handle()
    {
        $token = request()->all()['token'];
        $authToken = new AuthToken();
        $authToken->token = $token;
        $authToken->save();
        
        return redirect('https://www.wix.com/installer/install'. '?' . http_build_query([
            'token' => $token,
            'appId' => 'cde3294b-e695-4500-b3f8-59b00fd6516c',
            'redirectUrl' => 'https://brainly.uz/api/auth/handle/grand-code',
            'state' => $authToken->id
        ]));
    }
    public function handleCode()
    {
        $params = request()->all();
        $authToken = AuthToken::find($params['state']);
        $authToken->code = $params['code'];
        $authToken->instanceId = $params['instanceId'];
        $authToken->update();
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://www.wix.com/oauth/access',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "grant_type": "authorization_code",
            "client_id": "'. config('wix.app_id') .'",
            "client_secret": "'. config('wix.app_secret') .'",
            "code": "'. $authToken->code .'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type:  application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);
        $authToken->access_token = $response['access_token'];
        $authToken->refresh_token = $response['refresh_token'];
        $authToken->update();
        // to contiune process send post request and countiune...
        return redirect('https://www.wix.com/installer/token-received?' . http_build_query(['access_token' => $authToken->access_token]));
    }
    public function handleOrderPaid()
    {
        info(request()->all());
        return response()->json([
            'result' => 'ok'
        ]);
    }
}