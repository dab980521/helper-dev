<?php

namespace Tests\Unit\Resources;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testIndex(){
        $response = $this->get(route('articles.index'));

        $response->assertStatus(200);
    }

    public function testTree(){
        $response = $this->get(route('articles.tree',['id' => 5]));
        $response->assertStatus(200);
    }

    public function testStoreStatusCode(){
        $response = $this->post(route('articles.store'),[
            'title' => 'hello',
            'body' => "It's not bad",
        ]);

//        $response = $this->post(route('articles.store'));

        $response->assertStatus(201);
    }

//    public function testStore(){
//        $resopnse = $this->post(route('articles.store'));
//        echo $response->aseertS
//    }
}
