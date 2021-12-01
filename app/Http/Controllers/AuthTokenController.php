<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthTokenController extends Controller
{
    public function handle()
    {
        info(\raquest()->all());
        return 'ok';
    }
}