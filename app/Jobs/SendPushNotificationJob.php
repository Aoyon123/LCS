<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use App\Http\Helper\SendNotification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $FcmToken;
    public $title;
    public $body;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($FcmToken,$title,$body)
    {
        $this->FcmToken= $FcmToken;
        $this->title= $title;
        $this->body= $body;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sendNotification = new SendNotification();
        $sendNotification->sendNotification($this->FcmToken, $this->title, $this->body);
    }
}
