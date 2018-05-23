<?php
/**
 * Created by PhpStorm.
 * User: johnd
 * Date: 2018/5/23
 * Time: 下午3:29
 */

namespace App\Observers;

use App\Article;

class ArticleObserver{
    public function saving(Article $article){
        $article->body = clean($article->body,'user_article_body');
    }
}