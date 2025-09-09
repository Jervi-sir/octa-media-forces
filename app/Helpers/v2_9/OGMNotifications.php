<?php

namespace App\Helpers\v2_9;

class OGMNotifications
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public static function WelcomeNotification() 
    {
        return [
            'type' => 'welcome-notification',
            'title' => 'Welcome to the OGM club!',
            'content' => 'You’ve completed all the steps, your store is official, your badge is earned. Time to shine!'
        ];
    }
}
