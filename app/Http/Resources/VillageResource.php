<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VillageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->village_name,
            'pincode' => $this->pincode,
            'post_office' => $this->post_so_name,
            'taluka' => $this->taluka_name,
            'district' => $this->district_name,
            'state' => $this->state_name,
            'services' => $this->whenLoaded('services', function() {
                return $this->services->map(fn($s) => [
                    'code' => $s->code,
                    'name' => $s->name,
                    'is_available' => $s->pivot->is_available,
                ]);
            }),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
