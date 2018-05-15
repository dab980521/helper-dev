<?php

namespace App\Http\Controllers;

use App\Article;
use App\Http\Resources\ArticleResource;
use Illuminate\Http\Request;

class ArticlesController extends Controller
{
    public function index(Request $request){
        $onlyRoot = $request->input("onlyRoot");
        $model = Article::getModel();
        if ($onlyRoot){
            $model->where('isRoot',true);
        }
        return ArticleResource::collection($model->get());
    }
}
