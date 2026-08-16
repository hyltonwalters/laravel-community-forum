<?php

namespace Tests\Feature;

use App\Channel;
use App\Discussion;
use App\Notifications\NewReplyAdded;
use App\Reply;
use App\User;
use App\Watcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForumHardeningTest extends TestCase
{
    use RefreshDatabase;

    private int $userSequence = 0;

    private function createUser(array $attributes = []): User
    {
        $this->userSequence++;

        return User::create(array_merge([
            'name' => 'User '.$this->userSequence,
            'email' => 'user'.$this->userSequence.'@example.test',
            'avatar' => 'https://example.test/avatar.png',
            'password' => bcrypt('password'),
            'points' => 50,
        ], $attributes));
    }

    private function createChannel(): Channel
    {
        return Channel::create([
            'title' => 'General',
            'slug' => 'general',
        ]);
    }

    private function createDiscussion(User $owner, ?Channel $channel = null): Discussion
    {
        $channel ??= $this->createChannel();

        return Discussion::create([
            'user_id' => $owner->id,
            'channel_id' => $channel->id,
            'title' => 'Original discussion',
            'slug' => 'original-discussion',
            'content' => 'Original content',
        ]);
    }

    private function createReply(User $author, Discussion $discussion, string $content = 'A reply'): Reply
    {
        return Reply::create([
            'user_id' => $author->id,
            'discussion_id' => $discussion->id,
            'content' => $content,
        ]);
    }

    public function test_only_discussion_owner_can_update_discussion(): void
    {
        $owner = $this->createUser();
        $otherUser = $this->createUser();
        $channel = $this->createChannel();
        $discussion = $this->createDiscussion($owner, $channel);

        $this->actingAs($otherUser)
            ->put(route('discussions.update', $discussion), [
                'title' => 'Unauthorized change',
                'content' => 'Unauthorized content',
                'channel_id' => $channel->id,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('discussions', [
            'id' => $discussion->id,
            'title' => 'Original discussion',
        ]);

        $this->actingAs($owner)
            ->put(route('discussions.update', $discussion), [
                'title' => 'Updated discussion',
                'content' => 'Updated content',
                'channel_id' => $channel->id,
            ])
            ->assertRedirect('/forum');

        $this->assertDatabaseHas('discussions', [
            'id' => $discussion->id,
            'title' => 'Updated discussion',
            'slug' => 'updated-discussion',
        ]);
    }

    public function test_only_reply_owner_can_update_reply(): void
    {
        $discussionOwner = $this->createUser();
        $replyOwner = $this->createUser();
        $otherUser = $this->createUser();
        $discussion = $this->createDiscussion($discussionOwner);
        $reply = $this->createReply($replyOwner, $discussion);

        $this->actingAs($otherUser)
            ->put(route('reply.update', $reply->id), ['content' => 'Unauthorized'])
            ->assertForbidden();

        $this->actingAs($replyOwner)
            ->put(route('reply.update', $reply->id), ['content' => 'Updated reply'])
            ->assertRedirect(route('discussions.show', $discussion->slug));

        $this->assertDatabaseHas('replies', [
            'id' => $reply->id,
            'content' => 'Updated reply',
        ]);
    }

    public function test_reply_requires_content(): void
    {
        $owner = $this->createUser();
        $replier = $this->createUser();
        $discussion = $this->createDiscussion($owner);

        $this->actingAs($replier)
            ->from(route('discussions.show', $discussion))
            ->post(route('discussion.reply', $discussion->id), ['reply' => ''])
            ->assertRedirect(route('discussions.show', $discussion))
            ->assertSessionHasErrors('reply');

        $this->assertDatabaseCount('replies', 0);
    }

    public function test_watching_and_liking_are_idempotent(): void
    {
        $owner = $this->createUser();
        $user = $this->createUser();
        $discussion = $this->createDiscussion($owner);
        $reply = $this->createReply($owner, $discussion);

        $this->actingAs($user)->post(route('discussion.watch', $discussion->id));
        $this->actingAs($user)->post(route('discussion.watch', $discussion->id));

        $this->assertDatabaseCount('watchers', 1);

        $this->actingAs($user)->post(route('reply.like', $reply->id));
        $this->actingAs($user)->post(route('reply.like', $reply->id));

        $this->assertDatabaseCount('likes', 1);
    }

    public function test_only_discussion_owner_can_select_one_best_answer(): void
    {
        $owner = $this->createUser();
        $replyAuthor = $this->createUser();
        $otherUser = $this->createUser();
        $discussion = $this->createDiscussion($owner);
        $firstReply = $this->createReply($replyAuthor, $discussion, 'First');
        $secondReply = $this->createReply($replyAuthor, $discussion, 'Second');

        $this->actingAs($otherUser)
            ->patch(route('discussion.best_answer', $firstReply->id))
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('discussion.best_answer', $firstReply->id))
            ->assertRedirect();

        $this->assertDatabaseHas('replies', [
            'id' => $firstReply->id,
            'best_answer' => 1,
        ]);
        $this->assertSame(150, $replyAuthor->fresh()->points);

        $this->actingAs($owner)
            ->patch(route('discussion.best_answer', $secondReply->id))
            ->assertRedirect();

        $this->assertDatabaseHas('replies', [
            'id' => $secondReply->id,
            'best_answer' => 0,
        ]);
        $this->assertSame(150, $replyAuthor->fresh()->points);
    }

    public function test_reply_awards_points_and_notifies_other_watchers_only(): void
    {
        Notification::fake();

        $owner = $this->createUser();
        $replier = $this->createUser();
        $watcher = $this->createUser();
        $discussion = $this->createDiscussion($owner);

        Watcher::create(['discussion_id' => $discussion->id, 'user_id' => $watcher->id]);
        Watcher::create(['discussion_id' => $discussion->id, 'user_id' => $replier->id]);

        $this->actingAs($replier)
            ->post(route('discussion.reply', $discussion->id), ['reply' => 'Useful answer'])
            ->assertRedirect();

        $this->assertSame(75, $replier->fresh()->points);
        $this->assertDatabaseHas('replies', [
            'discussion_id' => $discussion->id,
            'user_id' => $replier->id,
            'content' => 'Useful answer',
        ]);

        Notification::assertSentTo($watcher, NewReplyAdded::class);
        Notification::assertNotSentTo($replier, NewReplyAdded::class);
    }

    public function test_guest_personal_filter_redirects_to_login_instead_of_erroring(): void
    {
        $this->get('/forum?filter=me')
            ->assertRedirect(route('login'));
    }

    public function test_channel_management_validates_titles_and_allows_unchanged_title_on_update(): void
    {
        $admin = $this->createUser();
        $admin->admin = true;
        $admin->save();
        $channel = $this->createChannel();

        $this->actingAs($admin)
            ->post(route('channels.store'), ['title' => ''])
            ->assertSessionHasErrors('title');

        $this->actingAs($admin)
            ->put(route('channels.update', $channel), ['title' => 'General'])
            ->assertRedirect('/channels');

        $this->assertDatabaseHas('channels', [
            'id' => $channel->id,
            'title' => 'General',
            'slug' => 'general',
        ]);
    }
}
