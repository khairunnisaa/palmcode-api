<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'member_id' => $this->member_id,
            'country_id' => $this->country_id,
            'id_verification_id' => $this->id_verification_id,
            'surfing_experience' => $this->surfing_experience,
            'visit_date' => $this->visit_date,
            'desired_board' => $this->desired_board,
        ];
    }
}
