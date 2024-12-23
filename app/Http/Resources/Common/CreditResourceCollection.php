<?php
namespace App\Http\Resources\Common;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CreditResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'total' => $this->collection->count(),
            'daysdiscount'=>\App\Models\Common\SiteMeta::where(['key'=>'advert_discount_days'])->first()->value,
            'data' => $this->collection
        ];
    }
}
