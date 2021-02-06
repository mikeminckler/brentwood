<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Livestream;
use App\Models\User;

class LivestreamReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $livestream;
    public $user;
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Livestream $livestream, User $user, $url)
    {
        $this->livestream = $livestream;
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.livestreams.reminder');
    }
}
