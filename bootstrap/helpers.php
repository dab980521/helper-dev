<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/24
 * Time: 上午10:29
 */

function api_token(){
    if (Auth::user()){
        $name = Auth::user()->name;
        return Cache::get($name);
    }
    return "";
}
