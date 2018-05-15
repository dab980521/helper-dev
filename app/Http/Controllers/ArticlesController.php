    public function index(Request $request){
        $onlyRoot = $request->input('onlyRoot');
        $model = Article::getModel();
        if ($onlyRoot)
        {
            $model = $model->where('isRoot',true);
        }
        return ArticleResource::collection($model->get());
    }
