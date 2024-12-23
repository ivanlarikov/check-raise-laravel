<?php

namespace App\Repositories\Notification;

use App\Models\Notification\Notification;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;

class NotificationRepository extends BaseRepository
{
    /**
     * @var Notification
     */
    protected Notification $notification;

    /**
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
        parent::__construct($notification);
    }
    
}
