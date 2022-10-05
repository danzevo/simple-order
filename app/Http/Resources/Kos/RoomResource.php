<?php

namespace App\Http\Resources\Kos;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
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
            'size' => $this->size,
            'total_room' => $this->total_room,
            'available_room' => $this->available_room,
        ];
    }
}
