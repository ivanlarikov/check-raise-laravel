<?php

namespace App\Observers\User;

use App\Models\User\UserProfile;

use App\Mail\Admin\NewPlayer;
use App\Mail\RoomManager\NewManager;
use App\Models\Notification\Notification;

use Illuminate\Support\Facades\Mail;

class UserProfileObserver
{
    /**
     * Handle the UserProfile "created" event.
     */
    public function created(UserProfile $userProfile): void
    {
        //send user verification email
        /*$userProfile->user->sendEmailVerificationNotification();

        if($userProfile->user->hasRole('Room Manager')){
            //admin notification
            $notification=Notification::where(['email_key'=>'new_rm_registration_admin','language'=>'en'])->first();
            Mail::to("keyur@example.com")->send(new NewManager($userProfile->user,$notification));
            //room manager notification 
            //Mail::to($userProfile->user->email)->send(new NewManager($userProfile->user));
        }*/
        
    }

    /**
     * Handle the UserProfile "updated" event.
     */
    public function updated(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "deleted" event.
     */
    public function deleted(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "restored" event.
     */
    public function restored(UserProfile $userProfile): void
    {
        //
    }

    /**
     * Handle the UserProfile "force deleted" event.
     */
    public function forceDeleted(UserProfile $userProfile): void
    {
        //
    }
}
