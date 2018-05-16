<?php

namespace Tests\Unit\Resources;

use App\Article;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ArticleTest extends TestCase
{
    use WithFaker;
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

    public function testStore(){
        // find a node who doesn't have a leftChild
        $id = Article::where('root','<>',0)
            ->where('leftChild',0)->first()->id;
        $response = $this->post(route('articles.store'),[
            'title' => $this->faker->text(10),
            'body' => $this->faker->text(100),
            'parentId' => $id,
            'type' => "left"
        ]);
        $response->assertStatus(201);
    }

    public function testDestroy(){
        // TODO: 需要优化
        $response = $this->withoutMiddleware()->delete(route('articles.destroy',['article' => 56]),[
            'id' => 56
        ]);
        $response->assertStatus(204);
    }
}
