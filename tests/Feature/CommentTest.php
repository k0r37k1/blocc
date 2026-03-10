<?php

namespace Tests\Feature;

use App\Filament\Resources\Comments\Pages\ListComments;
use App\Livewire\Comments;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\NewCommentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    protected Post $post;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->post = Post::factory()->published()->create();
        Setting::set('comments_enabled', '1');
    }

    public function test_comments_component_renders(): void
    {
        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertSuccessful();
    }

    public function test_shows_approved_comments(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertSee($comment->nickname)
            ->assertSee($comment->content);
    }

    public function test_hides_pending_comments(): void
    {
        $comment = Comment::factory()->pending()->create([
            'post_id' => $this->post->id,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertDontSee($comment->content);
    }

    public function test_submit_comment(): void
    {
        Notification::fake();

        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->set('nickname', 'Tester')
            ->set('email', 'test@example.com')
            ->set('content', 'This is a test comment.')
            ->call('submitComment')
            ->assertSet('successMessage', __('Comment sent! It will appear after approval.'));

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'nickname' => 'Tester',
            'email' => 'test@example.com',
            'content' => 'This is a test comment.',
            'is_approved' => false,
        ]);

        Notification::assertSentTo($this->admin, NewCommentNotification::class);
    }

    public function test_submit_reply(): void
    {
        $parent = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->call('startReply', $parent->id)
            ->set('nickname', 'Replier')
            ->set('content', 'This is a test reply.')
            ->call('submitComment')
            ->assertSet('successMessage', __('Reply sent! It will appear after approval.'));

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'parent_id' => $parent->id,
            'nickname' => 'Replier',
            'content' => 'This is a test reply.',
        ]);
    }

    public function test_honeypot_rejects_bots(): void
    {
        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->set('nickname', 'Bot')
            ->set('content', 'Spam content')
            ->set('website', 'http://spam.com')
            ->call('submitComment');

        $this->assertDatabaseMissing('comments', [
            'nickname' => 'Bot',
        ]);
    }

    public function test_time_check_rejects_fast_submissions(): void
    {
        // Mount sets formLoadedAt to time(), calling submit immediately = < 3 seconds
        $this->freezeTime();

        Livewire::test(Comments::class, ['post' => $this->post])
            ->set('nickname', 'FastBot')
            ->set('content', 'Too fast submission')
            ->call('submitComment');

        // Comment should not be in DB (silently rejected as spam)
        $this->assertDatabaseMissing('comments', [
            'nickname' => 'FastBot',
        ]);
    }

    public function test_excessive_links_rejected(): void
    {
        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->set('nickname', 'Spammer')
            ->set('content', 'Visit http://a.com and http://b.com and http://c.com')
            ->call('submitComment')
            ->assertHasErrors('content');
    }

    public function test_validation_requires_nickname_and_content(): void
    {
        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->set('nickname', '')
            ->set('content', '')
            ->call('submitComment')
            ->assertHasErrors(['nickname', 'content']);
    }

    public function test_edit_comment_with_valid_token(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
            'content' => 'Original content',
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('startEditing', $comment->id, $comment->edit_token)
            ->assertSet('editingCommentId', $comment->id)
            ->set('editContent', 'Updated content')
            ->call('saveEdit', $comment->edit_token);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => 'Updated content',
        ]);
    }

    public function test_edit_rejected_with_wrong_token(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('startEditing', $comment->id, 'wrong-token')
            ->assertSet('editingCommentId', null);
    }

    public function test_edit_rejected_after_60_minutes(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
            'created_at' => now()->subMinutes(61),
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('startEditing', $comment->id, $comment->edit_token)
            ->assertSet('editingCommentId', null);
    }

    public function test_delete_comment_with_valid_token(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('deleteComment', $comment->id, $comment->edit_token);

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_delete_rejected_after_60_minutes(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
            'created_at' => now()->subMinutes(61),
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('deleteComment', $comment->id, $comment->edit_token);

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_nested_replies_displayed(): void
    {
        $parent = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        $reply = Comment::factory()->create([
            'post_id' => $this->post->id,
            'parent_id' => $parent->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertSee($parent->content)
            ->assertSee($reply->content);
    }

    public function test_author_badge_shown(): void
    {
        Comment::factory()->asAuthor()->create([
            'post_id' => $this->post->id,
            'nickname' => 'Admin',
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertSee(__('Author'));
    }

    public function test_comment_count_on_post_card(): void
    {
        Comment::factory()->count(3)->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        $this->post->loadCount('approvedComments');

        $this->assertEquals(3, $this->post->approved_comments_count);
    }

    public function test_post_comments_disabled_hides_section(): void
    {
        $this->post->update(['comments_enabled' => false]);

        $this->get(route('blog.show', $this->post))
            ->assertDontSeeLivewire(Comments::class);
    }

    public function test_global_comments_disabled_hides_section(): void
    {
        Setting::set('comments_enabled', '0');

        $this->get(route('blog.show', $this->post))
            ->assertDontSeeLivewire(Comments::class);
    }

    // Filament Admin Tests

    public function test_filament_comments_list_renders(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(ListComments::class)
            ->assertSuccessful();
    }

    public function test_filament_shows_pending_badge(): void
    {
        Comment::factory()->pending()->count(3)->create([
            'post_id' => $this->post->id,
        ]);

        $badge = \App\Filament\Resources\Comments\CommentResource::getNavigationBadge();

        $this->assertEquals('3', $badge);
    }

    public function test_comment_model_is_editable_within_60_minutes(): void
    {
        $recent = Comment::factory()->create(['created_at' => now()->subMinutes(30)]);
        $old = Comment::factory()->create(['created_at' => now()->subMinutes(61)]);

        $this->assertTrue($recent->isEditable());
        $this->assertFalse($old->isEditable());
    }

    public function test_delete_rejected_with_wrong_token(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('deleteComment', $comment->id, 'wrong-token');

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_reply_parent_must_belong_to_same_post(): void
    {
        $otherPost = Post::factory()->published()->create();
        $otherComment = Comment::factory()->create([
            'post_id' => $otherPost->id,
            'is_approved' => true,
        ]);

        $component = Livewire::test(Comments::class, ['post' => $this->post]);

        $this->travel(5)->seconds();

        $component
            ->set('replyingTo', $otherComment->id)
            ->set('nickname', 'Tester')
            ->set('content', 'Reply to wrong post')
            ->call('submitComment');

        $this->assertDatabaseHas('comments', [
            'content' => 'Reply to wrong post',
            'parent_id' => null,
        ]);
    }

    public function test_admin_comment_auto_approved_and_marked_as_author(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->set('nickname', $this->admin->name)
            ->set('content', 'Admin comment')
            ->call('submitComment')
            ->assertSet('successMessage', __('Comment published.'));

        $this->assertDatabaseHas('comments', [
            'post_id' => $this->post->id,
            'nickname' => $this->admin->name,
            'content' => 'Admin comment',
            'is_approved' => true,
            'is_author' => true,
        ]);
    }

    public function test_admin_reply_auto_approved_and_marked_as_author(): void
    {
        $this->actingAs($this->admin);

        $parent = Comment::factory()->create([
            'post_id' => $this->post->id,
            'is_approved' => true,
        ]);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->call('startReply', $parent->id)
            ->set('content', 'Admin reply')
            ->call('submitComment')
            ->assertSet('successMessage', __('Reply published.'));

        $this->assertDatabaseHas('comments', [
            'parent_id' => $parent->id,
            'content' => 'Admin reply',
            'is_approved' => true,
            'is_author' => true,
        ]);
    }

    public function test_admin_skips_spam_check(): void
    {
        $this->actingAs($this->admin);

        $this->freezeTime();

        Livewire::test(Comments::class, ['post' => $this->post])
            ->set('content', 'Admin instant comment')
            ->call('submitComment')
            ->assertSet('successMessage', __('Comment published.'));

        $this->assertDatabaseHas('comments', [
            'content' => 'Admin instant comment',
        ]);
    }

    public function test_admin_nickname_prefilled(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(Comments::class, ['post' => $this->post])
            ->assertSet('nickname', $this->admin->name)
            ->assertSet('email', $this->admin->email);
    }
}
