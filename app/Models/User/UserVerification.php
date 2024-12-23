<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\belongsTo;

class UserVerification extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'user_id',
    'token',
    'email',
  ];

  /**
   * @return belongsTo
   */
  public function user(): belongsTo
  {
    return $this->belongsTo(User::class);
  }
}
