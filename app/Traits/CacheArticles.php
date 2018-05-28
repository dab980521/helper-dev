<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/28
 * Time: 下午6:26
 */

namespace App\Traits;


use App\Article;
use Illuminate\Support\Collection;

trait CacheArticles
{
    /**
     * @var Collection
     */
    protected $subtree;

    /**
     * 将整棵树以 hash map 的形式缓存在全局变量 $this->box 中
     * @param integer $rootId
     */
    protected function cacheArticlesCollection($rootId){
        $this->box = collect();
        $this->box = Article::where('root',$rootId)->get()->keyBy('id');
    }

    /**
     * @param $id
     */
    protected function cacheSubtree($id){
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