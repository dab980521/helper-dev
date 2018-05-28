<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/28
 * Time: 下午6:22
 */

namespace App\Traits;


use App\Article;
use Illuminate\Support\Collection;

trait TreeifyArticles
{
    use CacheArticles;
    /**
     * @var Collection
     */
    protected $box;


    public function tree($id) {
        // 清除缓存
        $this->box = collect();
        // 查找 root
        $root = Article::findOrFail($id)->root;
        // 获取树
        $this->cacheArticlesCollection($root);
        return $this->treeify($root);
    }

    protected function cacheArticlesCollection($rootId){
        $this->box = collect();
        $this->box = Article::where('root',$rootId)->get()->keyBy('id');
    }

    /**
     * @param string $node
     * @return Collection|array
     */
    protected function treeify($node){
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
}