<?php

namespace App;

use App\User;
use App\Channel;
use App\Watcher;
use Illuminate\Database\Eloquent\Model;

class Discussion extends Model
{
  protected $fillable = ['title', 'slug', 'content', 'user_id', 'channel_id'];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function channel()
  {
    return $this->belongsTo(Channel::class);
  }

  public function replies()
  {
    return $this->hasMany(Reply::class);
  }

  public function watchers()
  {
    return $this->hasMany(Watcher::class);
  }

  public function getRouteKeyName()
  {
    return 'slug';
  }

  public function is_being_watched_by_auth_user()
  {
    if (! auth()->check()) {
      return false;
    }

    return $this->watchers()->where('user_id', auth()->id())->exists();
  }

  public function hasBestAnswer()
  {
    return $this->replies()->where('best_answer', 1)->exists();
  }
}
