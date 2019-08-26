<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class Slack extends Notification
{
    use Queueable;
    protected $title;
    protected $attachmentTitle;
    protected $attachmentContent;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($title, $attachmentTitle = '', $attachmentContent = '')
    {
        $this->title = $title;
        $this->attachmentTitle = $attachmentTitle;
        $this->attachmentContent = $attachmentContent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the Slack representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return SlackMessage
     */
    public function toSlack($notifiable)
    {
        $slackMessage = (new SlackMessage)
                ->error()
                ->content($this->title);

        if (!empty($this->attachmentTitle)) {
            $slackMessage->attachment(function ($attachment) {
                $attachment->title($this->attachmentTitle)
                           ->content($this->attachmentContent);
            });
        }
        
        return $slackMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
