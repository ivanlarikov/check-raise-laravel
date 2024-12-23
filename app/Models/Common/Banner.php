<?php

namespace App\Models\Common;

use App\Models\Room\Room;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Banner extends Model
{
  use HasFactory;

  protected $fillable = [
    'room_id',
    'startdate',
    'enddate',
    'image'
  ];

  /**
   * @return belongsTo
   */
  public function room(): belongsTo
  {
    return $this->belongsTo(Room::class);
  }
}
