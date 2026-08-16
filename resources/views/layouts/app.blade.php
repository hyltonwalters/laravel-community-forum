<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'Laravel Community Forum') }}</title>

  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  @yield('css')
</head>
<body>
  <div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
          {{ config('app.name', 'Laravel Community Forum') }}
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto"></ul>

          <ul class="navbar-nav ms-auto">
            @guest
            <li class="nav-item">
              <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
            </li>
            @if (Route::has('register'))
            <li class="nav-item">
              <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
            </li>
            @endif
            @else
            <li class="nav-item dropdown">
              <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                {{ Auth::user()->name }}
              </a>

              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
                </form>
              </div>
            </li>
            @endguest
          </ul>
        </div>
      </div>
    </nav>

    <main class="container py-4">
      @if(session()->has('success'))
      <div class="alert alert-success" role="alert">{{ session()->get('success') }}</div>
      @endif
      @if(session()->has('error'))
      <div class="alert alert-danger" role="alert">{{ session()->get('error') }}</div>
      @endif

      <div class="row d-flex justify-content-center">
        <div class="col-md-4">
          @auth
          <a href="{{ route('discussions.create') }}" class="btn btn-outline-primary mb-4 w-100">Create a new discussion</a>
          @else
          <a href="{{ route('login') }}" class="btn btn-danger mb-4 w-100">Sign in to create a new discussion</a>
          @endauth

          <ul class="list-group mb-4">
            <li class="list-group-item">
              <a href="/forum" class="text-decoration-none">Forum</a>
            </li>
            @auth
            @if(auth()->user()->admin)
            <li class="list-group-item">
              <a href="{{ route('channels.index') }}" class="text-decoration-none">All Channels</a>
            </li>
            @endif
            <li class="list-group-item">
              <a href="/forum?filter=me" class="text-decoration-none">My Discussions</a>
            </li>
            <li class="list-group-item">
              <a href="/forum?filter=solved" class="text-decoration-none">Solved Discussions</a>
            </li>
            <li class="list-group-item">
              <a href="/forum?filter=unsolved" class="text-decoration-none">Unsolved Discussions</a>
            </li>
            @endauth
          </ul>

          <div class="card">
            <div class="card-header">Channels</div>
            <div class="card-body">
              <ul class="list-group">
                @foreach ($channels as $channel)
                <li class="list-group-item">
                  <a href="{{ route('channel', $channel->slug) }}" class="text-decoration-none">
                    {{ $channel->title }}
                  </a>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>

        <div class="col-md-8">
          @yield('content')
        </div>
      </div>
    </main>
  </div>

  @yield('scripts')
</body>
</html>
