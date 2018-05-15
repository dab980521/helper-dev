<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    public function getParentIdAttribute(){
        return User::where('leftChild',$this->id)
            ->orWhere('rightChild',$this->id)
            ->get();
    }
}
