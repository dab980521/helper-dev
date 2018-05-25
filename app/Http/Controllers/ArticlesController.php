<?php

namespace App\Http\Controllers;

use App\Article;
use App\Handlers\ImageUploadHandler;
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
            $model = $model->where('isRoot',true);
        }
        return ArticleResource::collection($model->get());
    }

    public function show(ArticleRequest $request){
        $article = Article::findOrFail($request->article);
        return new ArticleResource($article);
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
            "id" => $this->box[$node]->id,
            "children" => $children,
        ];
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
            'message' => '成功创建节点'
        ],201);
    }

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

    public function uploadImage(Request $request, ImageUploadHandler $uploader){
        $data = [
            'success' => false,
            'msg' => '上传失败',
            'file_path' => '',
        ];
        $status_code = 500;
         if ($file = $request->upload_file){
            $result = $uploader->save($request->upload_file, 'topics', \Auth::id(), 1024);
            if ($result){
                $data = [
                    'file_path' => $result['path'],
                    'msg' => '上传成功',
                    'success' => true,
                ];
                $status_code = 201;
            }
        }
        return response()->json($data,$status_code);
    }

    /**
     * 将整棵树以 hash map 的形式缓存在全局变量 $this->box 中
     * @param $rootId
     */
    private function cacheArticlesCollection($rootId){
        $this->box = collect();
        $this->box = Article::where('root',$rootId)->get()->keyBy('id');
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
