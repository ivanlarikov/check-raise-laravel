<?php

namespace App\Http\Controllers\Common;

use App\Services\Tournament\TournamentService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Common\Dashboard\DashboardResource;
use App\Http\Resources\Common\Dashboard\DashboardResourceCollection;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Resources\User\Room\RoomResource;
use App\Traits\Response\ResponseTrait;
use App\Services\Room\RoomService;


class DashboardController extends Controller
{
    //
    use ResponseTrait;
    /**
     * @var TournamentService
     */
    protected TournamentService $tournment;
    protected RoomService $room;
    /**
     * @param TournamentService $tournment
     */
    public function __construct(TournamentService $tournment, RoomService $room)
    {
        $this->tournment = $tournment;
        $this->room = $room;
    }

    /**
     * @return DashboardResourceCollection
     */
    public function index(): DashboardResourceCollection
    {
        return DashboardResourceCollection::make(
            $this->tournment->all(null, null, null, null, null, null, 100, true),
        );
    }
    public function show($id)
    {
        $data = $this->tournment->show(
            ['id' => $id]
        );

        if ($data) {
            return new DashboardResource(
                $data
            );
        }

        return $this->jsonResponseFail(
            trans('common/dashboard.show.fail')
        );
    }

    /**
     * @return RoomResourceCollection
     */
    public function roomshow($id): RoomResource
    {
        $room = $this->room->show(
            ['id' => $id]
        );

        if ($room) {
            return new RoomResource(
                $room
            );
        }

        return $this->jsonResponseFail(
            trans('common/dashboard.show.fail')
        );
    }
}
