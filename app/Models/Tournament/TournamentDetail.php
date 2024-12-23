<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;

class TournamentDetail extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'tournament_id',
    'type',
    'isshorthanded',
    'dealertype',
    'buyin',
    'bounty',
    'rake',
    'maxreentries',
    'reentriesrake',
    'startingstack',
    'level_duration',
    'maxplayers',
    'reservedplayers',
    'startday',
    'lastday',
    'lateregformat',
    'lateregtime',
    'latereground',
    'ischampionship',
    'bounusdeadline',
    'activelanguages',
    'reentry',
    'reentry_bounty',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'activelanguages' => 'array'
  ];

  public function tournament(): \Illuminate\Database\Eloquent\Relations\belongsTo
  {
    return $this->belongsTo(Tournament::class);
  }
}
