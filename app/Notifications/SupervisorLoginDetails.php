<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupervisorLoginDetails extends Notification
{
    use Queueable;

    protected $password;
    protected $loginUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $password, string $loginUrl)
    {
        $this->password = $password;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Supervisor Account Login Details')
            ->greeting('Welcome to the Project Allocation System!')
            ->line('Your supervisor account has been created successfully.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . $this->password)
            ->line('**Login URL:** ' . $this->loginUrl)
            ->line('Please log in and change your password for security reasons.')
            ->line('As a supervisor, you can:')
            ->line('• View and manage student projects')
            ->line('• Review project allocations')
            ->line('• Provide feedback on student work')
            ->action('Login Now', $this->loginUrl)
            ->line('If you have any questions, please contact the system administrator.')
            ->salutation('Best regards, Project Allocation System Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
