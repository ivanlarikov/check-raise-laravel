<?php

namespace App\Models\Tournament;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use App\Models\Room\Room;
use App\Models\User\User;

class Tournament extends Model
{
//  use HasSlug;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'title',
    'slug',
    'status',
    'user_id',
    'room_id',
    'disable_registration',
  ];

  protected $casts = [
    'disable_registration' => 'boolean'
  ];

  protected $with = [
    'detail',
    'description',
  ];

  protected $touches = ['room'];

  /**
   * Get the options for generating the slug.
   */
//  public function getSlugOptions(): SlugOptions
//  {
//    return SlugOptions::create()
//      ->generateSlugsFrom('title')
//      ->saveSlugsTo('slug');
//  }

  /**
   * @return HasOne
   */
  public function detail(): HasOne
  {
    return $this->HasOne(TournamentDetail::class);
  }

  /**
   * @return belongsTo
   */
  public function room(): belongsTo
  {
    return $this->belongsTo(Room::class);
  }

  /**
   * @return HasMany
   */
  public function description(): HasMany
  {
    return $this->HasMany(TournamentDescription::class);
  }

  /**
   * @return HasMany
   */
  public function structure(): HasMany
  {
    return $this->HasMany(TournamentStructure::class);
  }

  public function getroom()
  {
    return $this->room()->get();
  }

  /**
   * @return belongsToMany
   */
  public function registeredPlayers(): belongsToMany
  {
    return $this->belongsToMany(User::class, 'tournament_register_players')->withTimestamps();
  }

  /**
   * @return belongsToMany
   */
  public function waitingPlayers(): belongsToMany
  {
    return $this->belongsToMany(User::class, 'tournament_waiting_players')->withTimestamps();
  }

  /**
   * @return belongsToMany
   */
  public function checkinPlayers(): belongsToMany
  {
    return $this->belongsToMany(User::class, 'tournament_checkin_players')->withTimestamps();
  }

  /**
   * @return HasMany
   */
  public function latePlayers(): HasMany
  {
    return $this->HasMany(TournamentLateUser::class);
  }

  /**
   * @return HasMany
   */
  public function registration_log(): HasMany
  {
    return $this->HasMany(TournamentRegistrationLog::class)->orderBy('id', 'desc');
  }

  //TournamentRebuyCount
  public function rebuycount(): HasMany
  {
    return $this->HasMany(TournamentRebuyCount::class);
  }

  public function isRegistered($id)
  {
    return $this->registeredPlayers()->where('user_id', $id)->exists();
  }

  public function isWaiting($id)
  {
    return $this->waitingPlayers()->where('user_id', $id)->exists();
  }

  public function isCheckedin($id)
  {
    return $this->checkinPlayers()->where('user_id', $id)->exists();
  }

  public function isLate($id)
  {
    return $this->latePlayers()->where('user_id', $id)->exists();
  }

  public function getRakesum()
  {
    if (empty($this->detail))
      return 0;
    return ($this->checkinPlayers()->count() * $this->detail->rake);
  }

  public function getBuyInsum()
  {
    if (empty($this->detail))
      return 0;
    return ($this->checkinPlayers()->count() * $this->detail->buyin);
  }

  public function getReEntrysum()
  {
    if (empty($this->detail))
      return 0;
    return ($this->rebuycount()->count() * $this->detail->rake);
  }

  public function getBountyInsum()
  {
    if (empty($this->detail))
      return 0;
    return ($this->checkinPlayers()->count() * $this->detail->bounty);
  }


}
