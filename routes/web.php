<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('login/{provider}', 'SocialsController@redirect')->name('login.provider');
Route::get('login/{provider}/callback', 'SocialsController@Callback')->name('login.callback');

Route::resource('channels', 'ChannelsController')->except(['show']);
Route::get('/channel/{channel}', 'ForumController@channel')->name('channel');
Route::resource('discussions', 'DiscussionsController')->only(['create', 'store', 'show', 'edit', 'update']);
Route::resource('forum', 'ForumController')->only(['index']);

Route::middleware(['auth'])->group(function () {
    Route::post('discussion/watch/{id}', 'WatchersController@watch')->name('discussion.watch');
    Route::delete('discussion/watch/{id}', 'WatchersController@unwatch')->name('discussion.unwatch');
    Route::post('discussion/reply/{id}', 'DiscussionsController@reply')->name('discussion.reply');
    Route::post('/reply/like/{id}', 'RepliesController@like')->name('reply.like');
    Route::delete('/reply/like/{id}', 'RepliesController@unlike')->name('reply.unlike');
    Route::get('/reply/{id}/edit', 'RepliesController@edit')->name('reply.edit');
    Route::put('/reply/update/{id}', 'RepliesController@update')->name('reply.update');
    Route::patch('/discussion/reply/best-answer/{id}', 'RepliesController@best_answer')->name('discussion.best_answer');
});
