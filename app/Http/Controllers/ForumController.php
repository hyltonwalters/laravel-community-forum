<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Discussion;
use Illuminate\Pagination\Paginator;

class ForumController extends Controller
{
    public function index()
    {
        switch (request('filter')) {
            case 'me':
                if (!auth()->check()) {
                    return redirect()->route('login');
                }

                $results = Discussion::where('user_id', auth()->id())->paginate(3);
                break;

            case 'solved':
                $answered = [];
                foreach (Discussion::all() as $discussion) {
                    if ($discussion->hasBestAnswer()) {
                        $answered[] = $discussion;
                    }
                }
                $results = new Paginator($answered, 3);
                break;

            case 'unsolved':
                $unanswered = [];
                foreach (Discussion::all() as $discussion) {
                    if (!$discussion->hasBestAnswer()) {
                        $unanswered[] = $discussion;
                    }
                }
                $results = new Paginator($unanswered, 3);
                break;

            default:
                $results = Discussion::orderBy('created_at', 'desc')->paginate(3);
                break;
        }

        return view('forum')->with('discussions', $results);
    }

    public function channel(Channel $channel)
    {
        return view('channel')->with('discussions', $channel->discussions()->paginate(3));
    }
}
