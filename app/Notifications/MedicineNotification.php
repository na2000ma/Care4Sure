<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use JetBrains\PhpStorm\ArrayShape;

class MedicineNotification extends Notification
{
    use Queueable;
    private array $medicineData ;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $medicineData)
    {
        //
        $this->medicineData = $medicineData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line($this->medicineData['body'])
                    ->action($this->medicineData['medicineText'], $this->medicineData['medicineUrl'])
                    ->line($this->medicineData['thanks']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    #[ArrayShape(['medicine' => "array", 'title' => "string", 'subject' => "mixed", 'medicine_id' => "mixed"])]
    public function toArray(mixed $notifiable): array
    {
        return [
            //
<<<<<<< HEAD
            //'medicine'=>$this->medicineData,
            'title'=>'A new Medicine is expired',
=======
            'title'=>$this->medicineData['body'],
>>>>>>> 86a532fd6e5ba3d2207cd4257e8c7002cc93b8bb
            'subject'=>$this->medicineData['medicineText'],
            'medicine_id'=>$this->medicineData['medicine_id']
        ];
    }
}
