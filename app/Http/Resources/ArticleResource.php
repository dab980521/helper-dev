<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'title' => $this->title,
            'id' => $this->id,
            'body' => $this->body,
            'leftChild' => $this->leftChild,
            'rightChild' => $this->rightChild
        ];
    }
}
