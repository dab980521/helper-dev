<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/28
 * Time: 下午7:15
 */

namespace App\Traits;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait ApiToken
{
    protected function saveApiToken(){
        $user = Auth::user();
        $api_token = str_random(10);
        Auth::user()->api_token = $api_token;
        Cache::tags('users')->put($user->name, $api_token, 60);
    }
}