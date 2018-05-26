<?php

namespace Tests\Unit\Resources;

use App\Article;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        $id = Article::where("isRoot",true)->inRandomOrder()->get()->first()->id;
        $response = $this->get(route('articles.tree',['id' => $id]));
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

    public function testRandomStore(){
        $num = 5;
        foreach (range(0,$num) as $number){
            $childTypes = [
                0 => 'left',
                1 => 'right'
            ];
            $rand = rand()%2;
            $childType = $childTypes[$rand];
            $id = Article::where('root','<>',0)
                ->where($childType."Child",0)->inRandomOrder()->first()->id;
            $response = $this->post(route('articles.store'),[
                'title' => $this->faker->text(10),
                'body' => $this->faker->text(100),
                'parentId' => $id,
                'type' => $childType,
            ]);
            $response->assertStatus(201);
        }
    }

    public function testDestroy(){
        $article = 105;
        $id = $article;
        // TODO: 需要优化
        $response = $this->withoutMiddleware()->delete(route('articles.destroy',['article' => $article]),[
            'id' => $id
        ]);
        $response->assertStatus(204);
    }

    public function testDestroyWithMiddleware(){
        $article = 42;
        $id = $article;
        // TODO: 需要优化
        $response = $this->delete(route('articles.destroy',['article' => $article]),[
            'id' => $id
        ]);
        $response->assertStatus(204);

    }

    public function testCache(){
        Cache::put('key','value',3);
        $value = Cache::get('key');
        $this->assertEquals('value',$value);
    }

    public function testUpdate(){
        $id = 222;
        $title = $this->faker->text(10);
        $body = $this->faker->text(100);
        $response = $this->patch(route('articles.update',['article' => $id]), compact('title','body'));
        $response->assertStatus(201);
        $article = Article::findOrFail($id);
        $this->assertEquals($title,$article->title);
        $this->assertEquals($body,$article->body);
    }

    public function testNullReference(){
        $articles = Article::all()->keyBy('id');
        $articles->each(function($value, $key)use($articles){
            if ($value->leftChild){
                $this->assertTrue($articles->contains($value->leftChild));
                $this->assertEquals(Article::findOrFail($value->leftChild)->root,$value->root);
            }
            if ($value->rightChild){
                $this->assertTrue($articles->contains($value->rightChild));
                $this->assertEquals(Article::findOrFail($value->rightChild)->root,$value->root);
            }
        });
    }

    public function testInsertRootNode(){
        $title = $this->faker->text(10);
        $body = $this->faker->text(100);
        $isRoot = true;
        $article = Article::getModel();
        $article = $article->fill(compact('title','body','isRoot'));
        $article->save();
        $root = $article->id;
        $article->root = $root;
        $article->update();
        $data = Article::where("isRoot",true)->latest()->get()->first()->id;
        $this->assertEquals($data,$root);
    }

    public function testStoreRoot(){
        $title = $this->faker->text(10);
        $body = $this->faker->text(100);
        $response = $this->post(route('articles.store_root'),compact('title','body'));
        $response->assertStatus(201);
    }

    public function testUploadImage(){
        Storage::fake('avatars');
        $response = $this->json('POST',route('articles.upload_image'),[
            'upload_file' => UploadedFile::fake()->image('avatar.jpg')
        ]);
        $response->assertStatus(201);
    }

    public function testRemoveNode(){
        $biggerThan = 467;
        $articles = Article::where('id','>', $biggerThan)->get();
    }
}
