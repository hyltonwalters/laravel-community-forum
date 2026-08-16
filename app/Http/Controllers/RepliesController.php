<?php

namespace App\Http\Controllers;

use App\Like;
use App\Reply;
use Illuminate\Support\Facades\DB;

class RepliesController extends Controller
{
  public function like($id)
  {
    $reply = Reply::findOrFail($id);

    Like::firstOrCreate([
      'reply_id' => $reply->id,
      'user_id' => auth()->id(),
    ]);

    session()->flash('success', 'You liked the reply.');

    return redirect()->back();
  }

  public function unlike($id)
  {
    Like::where('reply_id', $id)
      ->where('user_id', auth()->id())
      ->delete();

    session()->flash('success', 'You unliked the reply.');

    return redirect()->back();
  }

  public function best_answer($id)
  {
    $reply = Reply::with('discussion', 'user')->findOrFail($id);

    abort_unless($reply->discussion->user_id === auth()->id(), 403);

    if ($reply->discussion->hasBestAnswer()) {
      return redirect()->back()->with('success', 'This discussion already has a best answer.');
    }

    DB::transaction(function () use ($reply) {
      $reply->best_answer = 1;
      $reply->save();

      $reply->user->increment('points', 100);
    });

    session()->flash('success', 'Reply has been marked as best answer.');

    return redirect()->back();
  }

  public function edit($id)
  {
    $reply = Reply::findOrFail($id);

    abort_unless($reply->user_id === auth()->id(), 403);

    return view('replies.edit')->with('reply', $reply);
  }

  public function update($id)
  {
    request()->validate([
      'content' => 'required|string',
    ]);

    $reply = Reply::findOrFail($id);

    abort_unless($reply->user_id === auth()->id(), 403);

    $reply->content = request()->content;
    $reply->save();

    session()->flash('success', 'Reply has been updated.');

    return redirect(route('discussions.show', $reply->discussion->slug));
  }
}
