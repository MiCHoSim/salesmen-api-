<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Salesman $resource
 */
class SalesmanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'self' => route('salesmen.show', $this->resource->id),
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'display_name' => $this->resource->display_name,
            'titles_before' => $this->resource->titles_before,
            'titles_after' => $this->resource->titles_after,
            'prosight_id' => $this->resource->prosight_id,
            'email' => $this->resource->email,
            'phone' => $this->resource->phone,
            'gender' => $this->resource->gender,
            'marital_status' => $this->resource->marital_status,
            'created_at' => $this->resource->created_at->toISOString(),
            'updated_at' => $this->resource->updated_at->toISOString(),
        ];
    }
}
