<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
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
            'name' => $this->name,
            'flag_url' => $this->flag_url,
            'code' => $this->code,
            'bookings' => BookingResource::collection($this->whenLoaded('bookings')),
        ];
    }
}
