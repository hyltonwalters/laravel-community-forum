<?php

namespace App\Http\Controllers;

use App\Discussion;
use App\Watcher;

class WatchersController extends Controller
{
    public function watch($id)
    {
      Discussion::findOrFail($id);

      Watcher::firstOrCreate([
        'discussion_id' => $id,
        'user_id' => auth()->id(),
      ]);

      session()->flash('success', 'You are watching this discussion.');

      return redirect()->back();
    }

    public function unwatch($id)
    {
      Watcher::where('discussion_id', $id)
        ->where('user_id', auth()->id())
        ->delete();

      session()->flash('success', 'You are no longer watching this discussion.');

      return redirect()->back();
    }
}
