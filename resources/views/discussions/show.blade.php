@extends('layouts.app')

@section('content')
@php($markdown = app(\App\Support\MarkdownRenderer::class))
<h3 class="card-header bg-light border-dark text-center">{{ $discussion->title }}</h3>
<div class="card my-4">
  <div class="card-header">
    <img src="{{ $discussion->user->avatar }}" width="40px" height="40px" style="border-radius:50%;"
      alt="{{ $discussion->user->name }}">&nbsp;&nbsp;&nbsp;
    <span>{{ $discussion->user->name }}</span>
    <span style="font-weight:bold;">( {{ $discussion->user->points }} )</span>
    @auth
    <div class="row float-right">
      <div class="col-md-6 p-0">
        @if ($discussion->is_being_watched_by_auth_user())
        <form action="{{ route('discussion.unwatch', $discussion->id) }}" method="POST" class="d-inline">
          @csrf
          @method('DELETE')
          <button type="submit" class="btn btn-outline-dark bg-white text-dark btn-sm mr-2">Unwatch</button>
        </form>
        @else
        <form action="{{ route('discussion.watch', $discussion->id) }}" method="POST" class="d-inline">
          @csrf
          <button type="submit" class="btn btn-outline-dark bg-white btn-sm text-dark mr-2">Watch</button>
        </form>
        @endif
      </div>
      @if($discussion->user_id == auth()->user()->id)
      @if(!$discussion->hasBestAnswer())
      <div class="col-md-6 p-0">
        <a href="{{ route('discussions.edit', $discussion->slug) }}" class="ml-3 btn btn-outline-info btn-sm">Edit</a>
      </div>
      @endif
      @endif
    </div>
    @endauth
  </div>

  <div class="card-body">
    <h4 class="text-center text-secondary">
      <b>{{ $discussion->title }}</b>
    </h4>
    <hr>
    <p class="text-center">
      {!! $markdown->render($discussion->content) !!}
    </p>
  </div>

  @if($best_answer)
  <hr>
  <div class="card border-info my-4 m-5">
    <div class="card-header bg-info">
      <img src="{{ $best_answer->user->avatar }}" width="40px" height="40px" style="border-radius:50%;"
        alt="{{ $best_answer->user->name }}">
      <span class="text-white">{{ $best_answer->user->name }}</span>
      <span style="font-weight:bold; color:white;">( {{ $best_answer->user->points }} )</span>
      <h3 class="text-center text-white pb-3">BEST ANSWER</h3>
    </div>
    <div class="card-body border-info">
      {!! $markdown->render($best_answer->content) !!}
    </div>
  </div>
  @endif

  <div class="card-footer">
    <span class="text-dark py-2 mb-0">
      <b>{{ $discussion->created_at->diffForHumans() }}</b>
    </span>
    <span class="float-right"><a href="{{ route('channel', $discussion->channel->slug) }}"
        class="btn btn-outline-dark text-dark bg-white btn-sm">{{ $discussion->channel->title }}</a></span>
  </div>
</div>

<h3 class="card-header bg-light border-dark text-center">Replies</h3>
@foreach ($discussion->replies as $r)
<div class="card my-4">
  <div class="card-header">
    <img src="{{ $r->user->avatar }}" width="40px" height="40px" style="border-radius:50%;"
      alt="{{ $r->user->name }}">&nbsp;&nbsp;&nbsp;
    <span>{{ $r->user->name }}</span>
    <span style="font-weight:bold;">( {{ $r->user->points }} )</span>
    @if(!$best_answer)
    @auth
    @if(auth()->user()->id == $r->user_id)
    @if(!$r->discussion->hasBestAnswer())
    <a href="{{ route('reply.edit', $r->id) }}" class="btn btn-sm btn-outline-info bg-white float-right ml-2">Edit</a>
    @endif
    @endif
    @if (auth()->user()->id == $discussion->user->id)
    <form action="{{ route('discussion.best_answer', $r->id) }}" method="POST" class="float-right">
      @csrf
      @method('PATCH')
      <button type="submit" class="btn btn-outline-dark bg-white text-dark btn-sm">Mark as best answer</button>
    </form>
    @endif
    @endauth
    @endif
  </div>

  <div class="card-body">
    <p class="text-center">
      {!! $markdown->render($r->content) !!}
    </p>
  </div>
  <div class="card-footer">

    @auth
    @if($r->is_liked_by_auth_user())
    <form action="{{ route('reply.unlike', $r->id) }}" method="POST" class="d-inline">
      @csrf
      @method('DELETE')
      <button type="submit" class="btn btn-danger btn-sm">Unlike &nbsp;<span
          class="badge badge-light">{{ $r->likes->count() }}</span></button>
    </form>
    @else
    <form action="{{ route('reply.like', $r->id) }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="btn btn-success btn-sm">Like &nbsp;<span
          class="badge badge-light">{{ $r->likes->count() }}</span></button>
    </form>
    @endif
    @endauth

  </div>
</div>
@endforeach

<form action="{{ route('discussion.reply', $discussion->id) }}" method="POST">
  @csrf
  <div class="card">
    <label for="reply" class="card-header w-100 border-secondary"><b>Leave a reply</b></label>
    <textarea name="reply" id="reply" cols="30" rows="10"
      class="card-body form-control @error('reply') is-invalid @enderror"></textarea>
    @error('reply')
    <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <div class="card-footer">
      @auth
      <button type="submit" class="btn btn-outline-dark text-dark bg-white float-right">Leave a reply</button>
      @else
      <a href="{{ route('login') }}" type="button" class="btn btn-danger text-white">Sign in to leave a reply!</a>
      @endauth
    </div>
  </div>
</form>
@endsection
