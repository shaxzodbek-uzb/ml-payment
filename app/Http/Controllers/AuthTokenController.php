<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function handle()
    {
        info(request()->all());
        return 'ok';
    }
}