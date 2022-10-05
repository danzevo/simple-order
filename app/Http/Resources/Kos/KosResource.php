<?php

namespace App\Http\Resources\Kos;

use Illuminate\Http\Resources\Json\JsonResource;

class KosResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'kos_type' => $this->kos_type,
            'description' => $this->description,
            'kos_established' => $this->kos_established,
            'room_type' => $this->room_type,
            'admin_name' => $this->admin_name,
        ];
    }
}
