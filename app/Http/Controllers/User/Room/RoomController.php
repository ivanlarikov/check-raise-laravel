<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use App\Services\Room\RoomService;
use App\Http\Resources\Room\RoomResourceCollection;
use App\Http\Requests\User\Room\RoomCreateRequest;
use App\Http\Requests\User\Room\RoomUpdateRequest;
use App\Http\Resources\User\Room\RoomResource;
use App\Models\Room\RoomDescription as RoomRoomDescription;
use App\Models\Room\Room;
use App\Models\Room\RoomDetail as RoomRoomDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

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
     * @param RoomResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): RoomResourceCollection
    {

        return RoomResourceCollection::make(
            $this->room->all(null, ['user_id' => $request->user()->id], null, null, null, null, 100, true)
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
        $room = $this->room->show(
            ['id' => $id, 'user_id' => $request->user()->id]
        );

        if ($room) {
            return new RoomResource(
                $room
            );
        }

        return $this->jsonResponseFail(
            trans('user.room/show.fail')
        );
    }
    public function store(RoomCreateRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $room = $this->room->create(
            $data
        );
        if ($room) {
            $room->description()->create($data);
            $room->detail()->create($data);

            return $this->jsonResponseSuccess(
                trans('user/room/room.create.success')
            );
        }

        return $this->jsonResponseFail(
            trans('user/room/room.create.fail'),
            400
        );
    }
    public function update(RoomUpdateRequest $request): \Illuminate\Http\JsonResponse
    {

        $data = $request->validated();

        if (!$this->room->isOwner($request->user(), $data['room']['id']))
            return $this->jsonResponseFail(
                trans('user/room/room.update.fail')
            );

        $room = $this->room->update(
            $data['room']['id'],
            $data['room']
        );
        /* update details */
        $room = $this->room->show(
            ['id' => $data['room']['id']]
        );
        if (!empty($data['details']['logo'])) {
            if (strlen($data['details']['logo']) > 30) {
                $data['details']['logo'] = $this->uploadImage($data['details']['logo']);
            }
        }
        $room->detail->update($data['details']);

        /* update description */
        foreach ($data['descriptions'] as $key => $item) {
            $room->description()->updateOrCreate(
                ['language' => $item['language']],
                ['language' => $item['language'], 'description' => $item['description']]
            );
        }

        return $this->jsonResponseSuccess(
            trans('user/room/room.update.success')
        );
    }

    public function updateStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        $status = $request->status;

        $room = $this->room->show(['user_id' => $request->user()->id]);
        $room->status = $status;
        $room->save();

        // when de-active room, unpublish all published tournaments of the room.
        if ($status == 2) {
            Tournament::where('room_id', '=', $room->id)->where('status', '=', 1)->update(['status' => 0]);
        }

        return $this->jsonResponseSuccess(
            trans('user/room/room.update.success')
        );
    }

    private function uploadImage($base64data)
    {
        $name = uniqid() . '.png';
        $file = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64data));
        Storage::disk('room')->put($name, $file);
        return $name;
    }

    public function subscription(Request $request)
    {
        $room = $this->room->show(
            ['user_id' => $request->user()->id]
        );

        if ($room) {
            return new RoomResource(
                $room
            );
        }

        return $this->jsonResponseFail(
            trans('user.room/show.fail')
        );
    }

    public function destroy($id)
    {
        // $this->room->delete($id);
        Room::where('id', "=", $id)->delete();
        RoomRoomDescription::where('room_id', "=", $id)->delete();
        RoomRoomDetail::where('room_id', "=", $id)->delete();
        return $this->jsonResponseSuccess(
            trans('user/room/room.delete.success')
        );
    }
    function check_base64_image($base64)
    {
        $img = imagecreatefromstring(base64_decode($base64));
        if (!$img) {
            return false;
        }

        imagepng($img, 'tmp.png');
        $info = getimagesize('tmp.png');

        unlink('tmp.png');

        if ($info[0] > 0 && $info[1] > 0 && $info['mime']) {
            return true;
        }

        return false;
    }
}
