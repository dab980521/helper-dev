<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title','body','root','rightChild','leftChild','isRoot'];

    public function getParentIdAttribute(){
        return Article::where('leftChild',$this->id)
            ->orWhere('rightChild',$this->id)
            ->first()->id;
    }
}
