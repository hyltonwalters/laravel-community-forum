<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueForumInteractionConstraints extends Migration
{
    public function up()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->unique(['reply_id', 'user_id'], 'likes_reply_user_unique');
        });

        Schema::table('watchers', function (Blueprint $table) {
            $table->unique(['discussion_id', 'user_id'], 'watchers_discussion_user_unique');
        });
    }

    public function down()
    {
        Schema::table('likes', function (Blueprint $table) {
            $table->dropUnique('likes_reply_user_unique');
        });

        Schema::table('watchers', function (Blueprint $table) {
            $table->dropUnique('watchers_discussion_user_unique');
        });
    }
}
