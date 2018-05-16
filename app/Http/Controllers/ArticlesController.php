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

    private function cacheArticlesCollection($rootId){
        $this->box = collect();
        $this->box = Article::where('root',$rootId)->get()->mapWithKeys(function($item){
            return [ $item->id => $item];
        });
    }

}
