<?php

namespace App\Services\Notification;

use App\Repositories\Notification\NotificationRepository;
use App\Services\BaseService;


class NotificationService extends BaseService
{
    /**
     * @var NotificationRepository
     */
    protected NotificationRepository $notification;

    /**
     * @param NotificationRepositoryc $notification
     */
    public function __construct(NotificationRepository $notification)
    {
        $this->notification = $notification;
        parent::__construct($this->notification);
    }

}
