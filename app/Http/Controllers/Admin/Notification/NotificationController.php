<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use App\Http\Resources\Notification\NotificationResourceCollection;
use App\Http\Resources\Notification\NotificationResource;

use App\Http\Requests\Admin\Notification\NotificationCreateRequest;
use App\Http\Requests\Admin\Notification\NotificationUpdateRequest;

use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ResponseTrait;
    /**
     * @var NotificationService
     */
    protected NotificationService $notification;


    /**
     * @param NotificationService $notification
     */
    public function __construct(NotificationService $notification)

    {
        $this->notification = $notification;
    }
    /**
     * @param NotificationResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): NotificationResourceCollection
    {

        return NotificationResourceCollection::make(
            $this->notification->all()
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $notification = $this->notification->show(
            ['id' => $id]
        );

        if ($notification) {
            return new NotificationResource(
                $notification
            );
        }

        return $this->jsonResponseFail(
            trans('admin.notification/show.fail')
        );
    }
    public function store(NotificationCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $notification = $this->notification->create(
            $data
        );
        if ($notification) {
            return $this->jsonResponseSuccess(
                trans('admin/notification/notification.create.success')
            );
        }

        return $this->jsonResponseFail(
            trans('admin/notification/notification.create.fail'),
            400
        );
    }
    public function update(NotificationUpdateRequest $request): \Illuminate\Http\JsonResponse
    {

        $data = $request->validated();
        $room = $this->notification->update(
            $data['id'],
            $data
        );
        return $this->jsonResponseSuccess(
            trans('admin/notification/notification.update.success')
        );
    }

    public function destroy($id)
    {

        $this->notification->delete($id);
        return $this->jsonResponseSuccess(
            trans('admin/notification/notification.delete.success')
        );
    }
}
