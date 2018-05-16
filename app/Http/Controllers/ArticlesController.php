<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ArticlesController extends Controller
{
    /**
     * @var Collection
     */
    private $box;

    /**
     * @var Collection
     */
    private $subtree;

    public function index(Request $request){
        $onlyRoot = $request->input("onlyRoot");
        $model = Article::getModel();
        if ($onlyRoot){
            $model->where('isRoot',true);
        }
        return ArticleResource::collection($model->get());
    }

    public function show($id){
        return "TODO:".$id ;
    }

    public function tree($id) {
        // 清除缓存
        $this->box = collect();
        // 查找 root
        $root = Article::findOrFail($id)->root;
        // 获取树
        $this->cacheArticlesCollection($root);
        return $this->treeify($root);
    }

    private function treeify($node){
        $children = collect();
        if ($this->box[$node]->leftChild){
            $children->push($this->treeify($this->box[$node]->leftChild));
        }
        if ($this->box[$node]->rightChild){
            $children->push($this->treeify($this->box[$node]->rightChild));
        }
        return [
            "title" => $this->box[$node]->title,
            "children" => $children
        ];
    }

    public function store(ArticleRequest $request, Article $article){

        // 参数列表
//        $attributes = [
//            'title',
//            'body',
//            'root',
//        ]; TODO: move this to phpdoc
        $title = $request->title;
        $body = $request->body;
        $parent = Article::findOrFail($request->parentId);
        $type = $request->type;
        if (($type === "left" and $parent->leftChild != 0) or ($type === 'right' and $parent->rightChild != 0)){
            return response()->json([
                'message' => '节点创建失败',
            ],423);
        }
        $root = $parent->root;
        // fill model
        $article->fill(compact('title','body','root'));
        // commit model
        $article->save();
        // update parent
        if ($request->type === "left"){
            $parent->leftChild = $article->id;
            $parent->update();
        }
        if ($request->type === "right"){
            $parent->rightChild = $article->id;
            $parent->update();
        }
        return response()->json([
            'message' => '成功创建节点'
        ],201);
    }

    public function destroy(ArticleRequest $request){
        $article = Article::findOrFail($request->id);
        $root = Article::findOrFail($article->id)->root;
        // 清除缓存
        $this->box = collect();
        $this->subtree = collect();
        // 缓存整树
        $this->cacheArticlesCollection($root);
        // 缓存子树
        $this->cacheSubtree($article->id);
        Article::destroy($this->subtree->toArray());
        Article::where('leftChild',$article->id)->update(['leftChild' => 0]);
        Article::where('rightChild',$article->id)->update(['rightChild' => 0]);
        return response()->json([
            'message' => '删除成功'
        ],204);
    }

    private function cacheArticlesCollection($rootId){
        $this->box = collect();
        $this->box = Article::where('root',$rootId)->get()->mapWithKeys(function($item){
            return [ $item->id => $item];
        });
    }

    private function cacheSubtree($id){
        $this->subtree->push($id);
        $left = $this->box[$id]->leftChild;
        $right = $this->box[$id]->rightChild;
        if ($left){
            $this->cacheSubtree($left);
        }
        if ($right){
            $this->cacheSubtree($right);
        }

    }
}
