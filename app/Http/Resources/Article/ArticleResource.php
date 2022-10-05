<?php

namespace App\Http\Resources\Article;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => enkrip($this->id),
            'title' => $this->title,
            'content' => $this->content,
            'thumbnail_image' => $this->thumbnail_image ? public_path('image\\'.$this->thumbnail_image) : public_path('image\\default-foto.png'),
            'user' => new UserResource($this->user)
        ];
    }
}
