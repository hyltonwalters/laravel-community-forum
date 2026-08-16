<?php

namespace App\Http\Controllers;

use App\User;
use App\Reply;
use App\Discussion;
use Illuminate\Support\Str;
use App\Notifications\NewReplyAdded;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Http\Requests\CreateDiscussionsRequest;
use App\Http\Requests\UpdateDiscussionsRequest;

class DiscussionsController extends Controller
{
  public function __construct()
  {
    $this->middleware(['auth'])->only(['edit', 'store', 'update', 'create']);
  }

  public function create()
  {
    return view('discussions.create');
  }

  public function store(CreateDiscussionsRequest $request)
  {
    auth()->user()->discussions()->create([
      'title' => $request->title,
      'content' => $request->content,
      'channel_id' => $request->channel_id,
      'slug' => Str::slug($request->title),
    ]);

    session()->flash('success', 'Discussion created successfully.');

    return redirect()->back();
  }

  public function show(Discussion $discussion)
  {
    $best_answer = $discussion->replies()->where('best_answer', 1)->first();

    return view('discussions.show')
      ->with('discussion', $discussion)
      ->with('best_answer', $best_answer);
  }

  public function edit(Discussion $discussion)
  {
    abort_unless($discussion->user_id === auth()->id(), 403);

    return view('discussions.create')->with('discussion', $discussion);
  }

  public function update(UpdateDiscussionsRequest $request, Discussion $discussion)
  {
    abort_unless($discussion->user_id === auth()->id(), 403);

    $discussion->title = $request->title;
    $discussion->content = $request->content;
    $discussion->channel_id = $request->channel_id;
    $discussion->slug = Str::slug($request->title);
    $discussion->save();

    session()->flash('success', 'Updated discussion successfully.');

    return redirect('/forum');
  }

  public function reply($id)
  {
    request()->validate([
      'reply' => 'required|string',
    ]);

    $discussion = Discussion::findOrFail($id);

    DB::transaction(function () use ($discussion) {
      $reply = Reply::create([
        'user_id' => auth()->id(),
        'discussion_id' => $discussion->id,
        'content' => request()->reply,
      ]);

      $reply->user->increment('points', 25);
    });

    $watchers = User::whereIn('id', $discussion->watchers()->pluck('user_id'))
      ->where('id', '!=', auth()->id())
      ->get();

    if ($watchers->isNotEmpty()) {
      Notification::send($watchers, new NewReplyAdded($discussion));
    }

    session()->flash('success', 'Replied to discussion.');

    return redirect()->back();
  }
}
