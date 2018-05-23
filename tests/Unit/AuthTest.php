<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/24
 * Time: 上午2:51
 */

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class AuthTest extends TestCase
{
    use WithFaker;
    public function testLogin(){
        $user = User::first();
        $response = $this->post(route('login.api'),[
            'name' => $user->name,
            'password' => 'secret'
        ]);
        $response->assertStatus(200);
    }
}
