<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    private $box;

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
        $articles= Article::where('root',$root)->get();
        // 将 Database Collection 改造成 ( $id => $item ) Hash Map
        $this->box = $articles->mapWithKeys(function($item){
            return [ $item->id => $item];
        });
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

    public function store(Request $request){
        $title = $request->input('title');
        $body = $request->input('body');
        $article = new Article(compact('title','body'));
//        $article->fill($request->all());
        $article->save();
        return response()->json([
            'message' => '成功创建节点'
        ],201);
    }
}
