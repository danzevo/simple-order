<?php

namespace App\Http\Resources\Kos;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Kos\{
    AddressResource,
    FacilityResource,
    KosImageResource,
    RoomResource,
};

class KosDetailResource extends JsonResource
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
            'address' => new AddressResource($this->address),
            'kos_image' => KosImageResource::collection($this->kosImage),
            'facility' => new FacilityResource($this->facility),
            'room' => auth()->check() ? new RoomResource($this->room) : null,
        ];
    }
}
