<?php

namespace App\Http\Controllers;

use App\Models\AuthToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthTokenController extends Controller
{
    public function handle()
    {
        $token = request()->all()['token'];
        $authToken = new AuthToken();
        $authToken->token = $token;
        $authToken->save();

        return redirect('https://www.wix.com/installer/install' . '?' . http_build_query([
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
            CURLOPT_POSTFIELDS => '{
            "grant_type": "authorization_code",
            "client_id": "' . config('wix.app_id') . '",
            "client_secret": "' . config('wix.app_secret') . '",
            "code": "' . $authToken->code . '"
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
        // $body = file_get_contents('php://input');
        $body = 'eyJraWQiOiJGYUJkNmZMMiIsImFsZyI6IlJTMjU2In0.eyJkYXRhIjoie1wib3JkZXJcIjp7XCJyZWFkXCI6ZmFsc2UsXCJudW1iZXJcIjoxMDA5NixcImxpbmVJdGVtc1wiOlt7XCJ3ZWlnaHRcIjpcIjE1XCIsXCJuYW1lXCI6XCJteSBwcm9kdWN0XCIsXCJxdWFudGl0eVwiOjIsXCJza3VcIjpcIjEyMzQ1Njc4XCIsXCJsaW5lSXRlbVR5cGVcIjpcIlBIWVNJQ0FMXCIsXCJ0YXhcIjpcIjFcIixcInByaWNlRGF0YVwiOntcInRheEluY2x1ZGVkSW5QcmljZVwiOmZhbHNlLFwicHJpY2VcIjpcIjVcIixcInRvdGFsUHJpY2VcIjpcIjEwXCJ9LFwib3B0aW9uc1wiOltdLFwiY3VzdG9tVGV4dEZpZWxkc1wiOltdLFwicHJvZHVjdElkXCI6XCJhMWY5ZDMzNy1mODMxLTQ1MjktMzFlNi02N2RiOGZkNGUxYWFcIixcImRpc2NvdW50XCI6XCIxXCIsXCJpbmRleFwiOjF9XSxcInBheW1lbnRTdGF0dXNcIjpcIlBBSURcIixcImxhc3RVcGRhdGVkXCI6XCIyMDIwLTAzLTE4VDE2OjQ3OjU5LjI0NFpcIixcImFyY2hpdmVkXCI6ZmFsc2UsXCJlbnRlcmVkQnlcIjp7XCJpZFwiOlwiZTg1Mjc0Y2YtMDQ3YS00OTg5LWJhZmYtZGFjNWUwYzM5NzFkXCIsXCJpZGVudGl0eVR5cGVcIjpcIlVTRVJcIn0sXCJzaGlwcGluZ0luZm9cIjp7XCJkZWxpdmVyeU9wdGlvblwiOlwiRXhwcmVzc1wiLFwiZXN0aW1hdGVkRGVsaXZlcnlUaW1lXCI6XCJUb2RheVwiLFwic2hpcHBpbmdSZWdpb25cIjpcIkVhc3QgY29hc3RcIixcInNoaXBtZW50RGV0YWlsc1wiOntcImFkZHJlc3NcIjp7XCJjaXR5XCI6XCJOZXcgWW9ya1wiLFwiZW1haWxcIjpcIkl2YW51c2hrYUBleGFtcGxlLmNvbVwiLFwiZnVsbE5hbWVcIjp7XCJmaXJzdE5hbWVcIjpcIkpvaG5cIixcImxhc3ROYW1lXCI6XCJTbWl0aFwifSxcInppcENvZGVcIjpcIjkyNTQ0XCIsXCJjb3VudHJ5XCI6XCJVU1wiLFwicGhvbmVcIjpcIis5NzIgNTU1MjM0NTU1XCJ9LFwiZGlzY291bnRcIjpcIjBcIixcInRheFwiOlwiMVwiLFwicHJpY2VEYXRhXCI6e1widGF4SW5jbHVkZWRJblByaWNlXCI6ZmFsc2UsXCJwcmljZVwiOlwiM1wifX19LFwiYWN0aXZpdGllc1wiOlt7XCJ0eXBlXCI6XCJPUkRFUl9QTEFDRURcIixcInRpbWVzdGFtcFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yMjJaXCJ9LHtcInR5cGVcIjpcIk9SREVSX1BBSURcIixcInRpbWVzdGFtcFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yNDRaXCJ9XSxcIndlaWdodFVuaXRcIjpcIkxCXCIsXCJpZFwiOlwiZmVkYjE5ZjUtYmQ0Yy00YmZjLWIyZDEtMjEyNTM4MzE5NjExXCIsXCJkYXRlQ3JlYXRlZFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yMjJaXCIsXCJiaWxsaW5nSW5mb1wiOntcInBheW1lbnRQcm92aWRlclRyYW5zYWN0aW9uSWRcIjpcInR4XzE4MDZcIixcInBheW1lbnRNZXRob2RcIjpcIlBheVBhbFwiLFwiYWRkcmVzc1wiOntcImNpdHlcIjpcIk5ldyBZb3JrXCIsXCJlbWFpbFwiOlwiSXZhbnVzaGthQGV4YW1wbGUuY29tXCIsXCJmdWxsTmFtZVwiOntcImZpcnN0TmFtZVwiOlwiSm9oblwiLFwibGFzdE5hbWVcIjpcIlNtaXRoXCJ9LFwiemlwQ29kZVwiOlwiOTI1NDRcIixcImNvdW50cnlcIjpcIlVTXCIsXCJwaG9uZVwiOlwiKzk3MiA1NTUyMzQ1NTVcIn0sXCJwYWlkRGF0ZVwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yNDRaXCIsXCJleHRlcm5hbFRyYW5zYWN0aW9uSWRcIjpcInR4XzE4MDZcIn0sXCJjdXJyZW5jeVwiOlwiVVNEXCIsXCJkaXNjb3VudFwiOntcInZhbHVlXCI6XCIxXCJ9LFwiYnV5ZXJMYW5ndWFnZVwiOlwiZW5cIixcImNoYW5uZWxJbmZvXCI6e1widHlwZVwiOlwiV0VCXCJ9LFwidG90YWxzXCI6e1wid2VpZ2h0XCI6XCIzMFwiLFwicXVhbnRpdHlcIjoyLFwidGF4XCI6XCIzXCIsXCJ0b3RhbFwiOlwiMTVcIixcInN1YnRvdGFsXCI6XCIxMFwiLFwiZGlzY291bnRcIjpcIjFcIixcInNoaXBwaW5nXCI6XCIzXCJ9LFwiZnVsZmlsbG1lbnRTdGF0dXNcIjpcIk5PVF9GVUxGSUxMRURcIixcImZ1bGZpbGxtZW50c1wiOltdfX0iLCJpYXQiOjE2Mzg5MDgzODIsImV4cCI6MTY0MjUwODM4Mn0.KImwbxQOIAlVo4Pikug_w2g8dKYVQQkG8ubBSxJJkOeVOCVLWPAxkqBJG0NvfdDO_FGFhfuIWdZ4v9Geavy280ZDZdFocQPdHOVnbSzWnh682viP28zPkDuJqDHhi1YzwCbQgB0uIONHaGBZFn9rz11boBz8tUdzNYY86Str-zvS2jC8rQ4QOsVtwHB6HKbi2l5A4brjIXyD4HoaQI0FJThbA1CWs_PcU1U286ZqMHchDAuHXvkbVP8l5RoECoW3kzLCK0TIWNL79S-xCkemx0IfPzR7F8vPLhAi9HhdQohmGw2FtcKrpxvT7LV0pR4y2dgImuXgN_COzR8nr24MEg';
        $body1 = 'eyJraWQiOiJGYUJkNmZMMiIsImFsZyI6IlJTMjU2In0.eyJkYXRhIjoie1wib3JkZXJcIjp7XCJyZWFkXCI6ZmFsc2UsXCJudW1iZXJcIjoxMDA5Nix1ImxpbmVJdGVtc1wiOlt7XCJ3ZWlnaHRcIjpcIjE1XCIsXCJuYW1lXCI6XCJteSBwcm9kdWN0XCIsXCJxdWFudGl0eVwiOjIsXCJza3VcIjpcIjEyMzQ1Njc4XCIsXCJsaW5lSXRlbVR5cGVcIjpcIlBIWVNJQ0FMXCIsXCJ0YXhcIjpcIjFcIixcInByaWNlRGF0YVwiOntcInRheEluY2x1ZGVkSW5QcmljZVwiOmZhbHNlLFwicHJpY2VcIjpcIjVcIixcInRvdGFsUHJpY2VcIjpcIjEwXCJ9LFwib3B0aW9uc1wiOltdLFwiY3VzdG9tVGV4dEZpZWxkc1wiOltdLFwicHJvZHVjdElkXCI6XCJhMWY5ZDMzNy1mODMxLTQ1MjktMzFlNi02N2RiOGZkNGUxYWFcIixcImRpc2NvdW50XCI6XCIxXCIsXCJpbmRleFwiOjF9XSxcInBheW1lbnRTdGF0dXNcIjpcIlBBSURcIixcImxhc3RVcGRhdGVkXCI6XCIyMDIwLTAzLTE4VDE2OjQ3OjU5LjI0NFpcIixcImFyY2hpdmVkXCI6ZmFsc2UsXCJlbnRlcmVkQnlcIjp7XCJpZFwiOlwiZTg1Mjc0Y2YtMDQ3YS00OTg5LWJhZmYtZGFjNWUwYzM5NzFkXCIsXCJpZGVudGl0eVR5cGVcIjpcIlVTRVJcIn0sXCJzaGlwcGluZ0luZm9cIjp7XCJkZWxpdmVyeU9wdGlvblwiOlwiRXhwcmVzc1wiLFwiZXN0aW1hdGVkRGVsaXZlcnlUaW1lXCI6XCJUb2RheVwiLFwic2hpcHBpbmdSZWdpb25cIjpcIkVhc3QgY29hc3RcIixcInNoaXBtZW50RGV0YWlsc1wiOntcImFkZHJlc3NcIjp7XCJjaXR5XCI6XCJOZXcgWW9ya1wiLFwiZW1haWxcIjpcIkl2YW51c2hrYUBleGFtcGxlLmNvbVwiLFwiZnVsbE5hbWVcIjp7XCJmaXJzdE5hbWVcIjpcIkpvaG5cIixcImxhc3ROYW1lXCI6XCJTbWl0aFwifSxcInppcENvZGVcIjpcIjkyNTQ0XCIsXCJjb3VudHJ5XCI6XCJVU1wiLFwicGhvbmVcIjpcIis5NzIgNTU1MjM0NTU1XCJ9LFwiZGlzY291bnRcIjpcIjBcIixcInRheFwiOlwiMVwiLFwicHJpY2VEYXRhXCI6e1widGF4SW5jbHVkZWRJblByaWNlXCI6ZmFsc2UsXCJwcmljZVwiOlwiM1wifX19LFwiYWN0aXZpdGllc1wiOlt7XCJ0eXBlXCI6XCJPUkRFUl9QTEFDRURcIixcInRpbWVzdGFtcFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yMjJaXCJ9LHtcInR5cGVcIjpcIk9SREVSX1BBSURcIixcInRpbWVzdGFtcFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yNDRaXCJ9XSxcIndlaWdodFVuaXRcIjpcIkxCXCIsXCJpZFwiOlwiZmVkYjE5ZjUtYmQ0Yy00YmZjLWIyZDEtMjEyNTM4MzE5NjExXCIsXCJkYXRlQ3JlYXRlZFwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yMjJaXCIsXCJiaWxsaW5nSW5mb1wiOntcInBheW1lbnRQcm92aWRlclRyYW5zYWN0aW9uSWRcIjpcInR4XzE4MDZcIixcInBheW1lbnRNZXRob2RcIjpcIlBheVBhbFwiLFwiYWRkcmVzc1wiOntcImNpdHlcIjpcIk5ldyBZb3JrXCIsXCJlbWFpbFwiOlwiSXZhbnVzaGthQGV4YW1wbGUuY29tXCIsXCJmdWxsTmFtZVwiOntcImZpcnN0TmFtZVwiOlwiSm9oblwiLFwibGFzdE5hbWVcIjpcIlNtaXRoXCJ9LFwiemlwQ29kZVwiOlwiOTI1NDRcIixcImNvdW50cnlcIjpcIlVTXCIsXCJwaG9uZVwiOlwiKzk3MiA1NTUyMzQ1NTVcIn0sXCJwYWlkRGF0ZVwiOlwiMjAyMC0wMy0xOFQxNjo0Nzo1OS4yNDRaXCIsXCJleHRlcm5hbFRyYW5zYWN0aW9uSWRcIjpcInR4XzE4MDZcIn0sXCJjdXJyZW5jeVwiOlwiVVNEXCIsXCJkaXNjb3VudFwiOntcInZhbHVlXCI6XCIxXCJ9LFwiYnV5ZXJMYW5ndWFnZVwiOlwiZW5cIixcImNoYW5uZWxJbmZvXCI6e1widHlwZVwiOlwiV0VCXCJ9LFwidG90YWxzXCI6e1wid2VpZ2h0XCI6XCIzMFwiLFwicXVhbnRpdHlcIjoyLFwidGF4XCI6XCIzXCIsXCJ0b3RhbFwiOlwiMTVcIixcInN1YnRvdGFsXCI6XCIxMFwiLFwiZGlzY291bnRcIjpcIjFcIixcInNoaXBwaW5nXCI6XCIzXCJ9LFwiZnVsZmlsbG1lbnRTdGF0dXNcIjpcIk5PVF9GVUxGSUxMRURcIixcImZ1bGZpbGxtZW50c1wiOltdfX0iLCJpYXQiOjE2Mzg5MDgzODIsImV4cCI6MTY0MjUwODM4Mn0.KImwbxQOIAlVo4Pikug_w2g8dKYVQQkG8ubBSxJJkOeVOCVLWPAxkqBJG0NvfdDO_FGFhfuIWdZ4v9Geavy280ZDZdFocQPdHOVnbSzWnh682viP28zPkDuJqDHhi1YzwCbQgB0uIONHaGBZFn9rz11boBz8tUdzNYY86Str-zvS2jC8rQ4QOsVtwHB6HKbi2l5A4brjIXyD4HoaQI0FJThbA1CWs_PcU1U286ZqMHchDAuHXvkbVP8l5RoECoW3kzLCK0TIWNL79S-xCkemx0IfPzR7F8vPLhAi9HhdQohmGw2FtcKrpxvT7LV0pR4y2dgImuXgN_COzR8nr24MEg';

        $publicKey = <<<EOD
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA3k8FyPhN1ROCjQH2gwH0
/035BPcDvGisibLhV6nyWECsxaF9WEHSnOGXmIyXRZLX7d9qdaD9eMqj/uztIEMF
q+4rqaNhP4jOP+nm7hIWhKZJc6oEMR8HlBipzpBby2qyY3hc6hxsPoYBX3hvb35l
J1aNPQUg1QSYCFjEYW1NaDjnxpNSHQmyvrQrxhcp+/6/3Af/LKW1eHeyvSJcMFcK
5X4MMjHNvD/GKqch7uD8QB/Uo1MQV3VoWBsdJsqOfPgUogDgh7qCyWQlKOsBScSc
/FIACZmLng2EHaayQbryQpnzG40s3+Le1E4W4bmJLw/cbF2UQMCyrWJdV3zXqZig
vQIDAQAB
-----END PUBLIC KEY-----
EOD;
        $decoded = JWT::decode($body, new Key($publicKey, 'RS256'));
        info($decoded);
        $decoded = JWT::decode($body1, new Key($publicKey, 'RS256'));
        info(
            $decoded
        );
        return response()->json([
            'result' => $decoded
        ]);
    }
}