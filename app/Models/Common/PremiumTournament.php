<?php

namespace App\Models\Common;

use App\Models\Room\Room;
use App\Models\Tournament\Tournament;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PremiumTournament extends Model
{
  use HasFactory;

  protected $fillable = [
    'room_id',
    'tournament_id',
    'startdate',
    'enddate',
  ];

  /**
   * @return belongsTo
   */
  public function room(): belongsTo
  {
    return $this->belongsTo(Room::class);
  }

  /**
   * @return BelongsTo
   */
  public function tournament(): belongsTo
  {
    return $this->belongsTo(Tournament::class);
  }
}
