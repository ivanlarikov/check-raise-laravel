<?php

namespace App\Models\Room;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;
use Illuminate\Database\Eloquent\Relations\belongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Models\Tournament\Tournament;
use App\Models\User\User;

class Room extends Model
{
  use HasSlug;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'title',
    'slug',
    'user_id',
  ];

  /**
   * @var string[]
   */
  protected $with = [
    /*'description',*/
    'detail',
  ];

  /**
   * Get the options for generating the slug.
   */
  public function getSlugOptions(): SlugOptions
  {
    return SlugOptions::create()
      ->generateSlugsFrom('title')
      ->saveSlugsTo('slug');
  }

  /**
   * @return HasMany
   */
  public function description(): HasMany
  {
    return $this->HasMany(RoomDescription::class);
  }

  /**
   * @return HasOne
   */
  public function detail(): HasOne
  {
    return $this->HasOne(RoomDetail::class);
  }

  /**
   * @return HasMany
   */
  public function tournaments(): HasMany
  {
    return $this->HasMany(Tournament::class);
  }

  public function getActivetournaments(): Collection
  {
    return $this->tournaments()->where(['closed' => 0, 'archived' => 0, 'status' => 1])->get();
  }

  /**
   * @return belongsToMany
   */
  public function room_users(): belongsToMany
  {
    return $this->belongsToMany(User::class, 'room_users')->withPivot('is_suspend', 'id_checked');
  }

  /**
   * @return HasMany
   */
  public function room_directors(): HasMany
  {
    return $this->HasMany(RoomDirector::class);
  }

  /**
   * @return belongsTo
   */
  public function manager(): belongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * @return HasOne
   */
  public function setting(): HasOne
  {
    return $this->hasOne(RoomSetting::class);
  }
}
