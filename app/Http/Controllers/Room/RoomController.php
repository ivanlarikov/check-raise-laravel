<?php

namespace App\Http\Controllers\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Resources\Room\RoomResource;
use App\Models\Room\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\Response\ResponseTrait;

class RoomController extends Controller
{
    use ResponseTrait;
    /**
     * @var RoomService
     */
    protected RoomService $room;

    /**
     * @param RoomService $tournment
     */
    public function __construct(RoomService $room)
    {
        $this->room = $room;
    }

    /**
     * @return RoomResourceCollection
     */
    public function index(Request $request)
    {
        $query = Room::query();

        $statues = explode(',', $request->status);

        if (!empty($request->status)) {
            $query->whereIn('status', $statues);
        }

        $rooms = $query->limit(100)->paginate();

        //
        return RoomResourceCollection::make($rooms);
        // return RoomResourceCollection::make(
        //     $this->room->all(null, null, null, null, null, null, 100, true)
        // );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return RoomResource
     */
    public function show($id)
    {
        $room = $this->room->show(
            ['slug' => $id]
        );

        if ($room) {
            return new RoomResource(
                $room
            );
        }

        return $this->jsonResponseFail(
            trans('room/show.fail')
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
