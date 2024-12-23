<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\User\UserProfile;
use App\Models\Room\Room;
use App\Models\Tournament\Tournament;
use App\Models\Tournament\TournamentRebuyCount;
use App\Models\User\DirectorCapability;
use App\Models\Room\RoomDirector;
use App\Models\Tournament\TournamentRegisterPlayer;
use App\Models\Tournament\TournamentLateUser;
use App\Models\Tournament\TournamentDetail;

class User extends Authenticatable
{
  use HasApiTokens, HasFactory, HasRoles, Notifiable;

  protected array $guard_name = ['web'];

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'username',
    'email',
    'password',
    'email_verified_at',
    'status'
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  protected $with = [
    'directory_capabilities'
  ];

  public function profile(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->HasOne(UserProfile::class);
  }

  public function room(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->HasOne(Room::class);
  }

  public function directory_capabilities()
  {
    return $this->HasMany(DirectorCapability::class);
  }
  /*public function isOwner($roomid)
    {
        return $this->HasMany(Room::class);
    }*/
  /**
   * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
   */
  public function registeredTournaments(): \Illuminate\Database\Eloquent\Relations\belongsToMany
  {
    return $this->belongsToMany(Tournament::class, 'tournament_register_players');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function checkinPlayers(): \Illuminate\Database\Eloquent\Relations\belongsToMany
  {
    return $this->belongsToMany(Tournament::class, 'tournament_checkin_players');
  }

  public function isCheckedin($id)
  {
    return $this->checkinPlayers()->where('tournament_id', $id)->exists();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function reentry(): \Illuminate\Database\Eloquent\Relations\HasMany
  {
    return $this->HasMany(TournamentRebuyCount::class);
  }

  public function getUserrebuycount($tournament_id)
  {
    return $this->reentry()->where('tournament_id', $tournament_id)->pluck('rebuycount')->first();
  }

  public function director_room(): \Illuminate\Database\Eloquent\Relations\HasOne
  {
    return $this->HasOne(RoomDirector::class);
  }
  public function last_register($t, $u)
  {
    $date = TournamentRegisterPlayer::select('created_at')->where('tournament_id', $t)->where('user_id', $u)->orderBy('id', 'desc')->first();
    if (!empty($date)) {
      return $date;
    } else {
      return '-';
    }
  }
  public function getTournaments($id)
  {
    if (empty($id)) $id = 3182;
    $date = TournamentDetail::where('tournament_id', $id)->first();
    if (!empty($date)) {
      return $date;
    } else {
      return '-';
    }
  }
  public function lateannouncement($t, $u)
  {
    $date = TournamentLateUser::select('latetime')->where('tournament_id', $t)->where('user_id', $u)->first();
    if (!empty($date)) {
      $late = substr($date->latetime, 0, -3);
      return $late;
    } else {
      return '';
    }
  }
}
