<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Comment $comment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $post = $this->comment->post;
        $postUrl = route('blog.show', $post);

        return (new MailMessage)
            ->subject(__('New comment on ":title"', ['title' => $post->title]))
            ->greeting(__('New comment!'))
            ->line(__(':nickname wrote a comment on ":title":', [
                'nickname' => $this->comment->nickname,
                'title' => $post->title,
            ]))
            ->line('"'.\Illuminate\Support\Str::limit($this->comment->content, 200).'"')
            ->action(__('View Post'), $postUrl)
            ->line(__('The comment is awaiting moderation.'));
    }
}
