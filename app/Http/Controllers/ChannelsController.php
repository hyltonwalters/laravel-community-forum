<?php

namespace App\Http\Controllers;

use App\Channel;
use App\Http\Requests\CreateChannelsRequest;
use Illuminate\Support\Str;

class ChannelsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        return view('channels.index');
    }

    public function create()
    {
        return view('channels.create');
    }

    public function store(CreateChannelsRequest $request)
    {
        $title = $request->validated()['title'];

        Channel::create([
            'title' => $title,
            'slug' => Str::slug($title),
        ]);

        session()->flash('success', 'Channel created successfully.');

        return redirect('/channels');
    }

    public function edit(Channel $channel)
    {
        return view('channels.create')->with('channel', $channel);
    }

    public function update(CreateChannelsRequest $request, Channel $channel)
    {
        $title = $request->validated()['title'];

        $channel->title = $title;
        $channel->slug = Str::slug($title);
        $channel->save();

        session()->flash('success', 'Channel updated successfully.');

        return redirect('/channels');
    }

    public function destroy(Channel $channel)
    {
        $channel->delete();

        session()->flash('success', 'Channel deleted successfully.');

        return redirect('/channels');
    }
}
