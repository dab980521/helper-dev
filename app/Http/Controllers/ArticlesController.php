<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Requests\ArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Traits\CacheArticles;
use App\Traits\TreeifyArticles;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    use TreeifyArticles;
    use CacheArticles;

    /**
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request){
        $onlyRoot = $request->input("onlyRoot");
        $model = Article::getModel();
        if ($onlyRoot){
            $model = $model->where('isRoot',true);
        }
        return ArticleResource::collection($model->get());
    }

    /**
     * @param ArticleRequest $request
     * @return ArticleResource
     */
    public function show(ArticleRequest $request){
        $article = Article::findOrFail($request->article);
        return new ArticleResource($article);
    }

    /**
     * 第二参数*仅*用作创建空模型
     * 模型必需的参数：title, body, root
     *
     * @param ArticleRequest $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ArticleRequest $request, Article $article){
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
        try {
            \DB::transaction(function () use (&$article, &$request, &$parent){
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
            });
        }catch (\Throwable $exception){
            return response()->json([
                'message' => '节点创建失败, 所有操作已回滚',
                'code' => $exception->getCode(),
            ], 500);
        }
        return response()->json([
            'message' => '成功创建节点',
            'data' => [
                'id' => $article->id
            ]
        ],201);
    }

    /**
     * @param Request $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_root(Request $request, Article $article){

        $title = $request->title;
        $body = $request->body;
        $isRoot = true;

        // fill model
        $article->fill(compact('title','body','isRoot'));
        try {
            \DB::transaction(function () use (&$article){
                // commit model
                $article->save();
                $article->root = $article->id;
                $article->update();
            });
        }catch (\Throwable $exception){
            return response()->json([
                'message' => '根节点创建失败, 所有操作已回滚',
                'code' => $exception->getCode(),
                'error' => $exception->getMessage()
            ], 500);
        }
        return response()->json([
            'message' => '成功创建节点'
        ],201);
    }


    /**
     * @param ArticleRequest $request
     * @param Article $article
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(ArticleRequest $request, Article $article){
        // fill model
        $article->fill($request->all());
        try {
            \DB::transaction(function () use (&$article){
                $article->save();
            });
        }catch (\Throwable $exception){
            return response()->json([
                'message' => '节点创建失败, 所有操作已回滚',
                'code' => $exception->getCode(),
            ], 500);
        }
        return response()->json([
            'message' => '成功创建节点'
        ],201);
    }

    /**
     * @param ArticleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ArticleRequest $request){
        $article = Article::findOrFail($request->id);
        $root = Article::findOrFail($article->id)->root;
        // 清除缓存
        $this->box = collect();
        $this->subtree = collect();
        // 缓存
        $this->cacheArticlesCollection($root);
        $this->cacheSubtree($article->id);
        try {
            \DB::transaction(function () use ($article){
                Article::destroy($this->subtree->toArray());
                // 解引用
                Article::where('leftChild',$article->id)->update(['leftChild' => 0]);
                Article::where('rightChild',$article->id)->update(['rightChild' => 0]);
            });
        }catch (\Throwable $exception){
            return response()->json([
                'message' => '删除失败, 所有操作已回滚'
            ],500);
        }
        return response()->json([
            'message' => '删除成功'
        ],204);
    }
}
