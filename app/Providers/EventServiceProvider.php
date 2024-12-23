<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Observers\User\UserProfileObserver;
use App\Models\User\UserProfile;

use App\Observers\Tournament\TournamentObserver;
use App\Models\Tournament\Tournament;
use App\Observers\Tournament\TournamentDetailObserver;
use App\Models\Tournament\TournamentDetail;

class EventServiceProvider extends ServiceProvider
{

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        UserProfile::class => [UserProfileObserver::class],
        Tournament::class => [TournamentObserver::class],
        TournamentDetail::class => [TournamentDetailObserver::class],
    ];
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        /*UserProfile::observe(UserProfileObserver::class);
        Tournament::observe(UserProfileObserver::class);*/
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
