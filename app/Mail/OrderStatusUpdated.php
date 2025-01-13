<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;
    public $additionalMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order, $additionalMessage)
    {
        $this->order = $order;
        $this->additionalMessage = $additionalMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("Your Order Status Has Been Updated")
                    ->view('emails.order_status_updated');
    }
}
