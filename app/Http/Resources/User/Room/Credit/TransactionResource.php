<?php

namespace App\Http\Resources\User\Room\Credit;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'id'=>$this->id,
            'description'=>$this->description,
            'amount'=>$this->amount,
            //'paypalorderid'=>$this->paypalorderid,
            'created_at'=>$this->created_at,
        ];
    }
}
