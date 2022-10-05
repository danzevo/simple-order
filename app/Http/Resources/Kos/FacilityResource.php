<?php

namespace App\Http\Resources\Kos;

use Illuminate\Http\Resources\Json\JsonResource;

class FacilityResource extends JsonResource
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
            'public_facility' => $this->public_facility,
            'room_facility' => $this->room_facility,
            'bath_facility' => $this->bath_facility,
            'park_facility' => $this->park_facility,
        ];
    }
}
