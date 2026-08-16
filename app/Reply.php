<?php

namespace App;

use App\Like;
use App\User;
use App\Discussion;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
  protected $fillable = ['content', 'user_id', 'discussion_id', 'best_answer'];

  public function discussion()
  {
    return $this->belongsTo(Discussion::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function likes()
  {
    return $this->hasMany(Like::class);
  }

  public function is_liked_by_auth_user()
  {
    if (! auth()->check()) {
      return false;
    }

    return $this->likes()->where('user_id', auth()->id())->exists();
  }
}
