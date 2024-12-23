<?php

namespace App\Http\Controllers\Player\Tournament;

use App\Http\Controllers\Controller;
use App\Services\Tournament\TournamentService;
use App\Services\User\UserService;
use App\Http\Resources\Tournament\TournamentResourceCollection;
use App\Http\Resources\Tournament\TournamentResource;
use App\Http\Resources\Tournament\TournamentDetailResource;
use App\Http\Requests\User\Tournament\TournamentRegisterRequest;
use App\Models\Tournament\TournamentDescription;
use App\Models\Tournament\TournamentDetail;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LateArrivalController extends Controller
{
    use ResponseTrait;
    /**
     * @var TournamentService
     */
    protected TournamentService $touranment;
    protected UserService $user;

    /**
     * @param TournamentService $touranment
     */
    public function __construct(TournamentService $touranment,UserService $user)
    {
        $this->touranment = $touranment;
        $this->user = $user;
    }

    /**
     * @return TournamentResourceCollection
     */
    public function announce(Request $request,$id)
    {
        if(empty($request->latetime)){
            return $this->jsonResponseFail(
                trans('tournament/latearraival.create.fail')
            );
        }
        $touranment = $this->touranment->show(
            ['id' => $id]
        );
        if (empty($touranment)) {
            return $this->jsonResponseFail(
                trans('tournament/latearraival.create.fail')
            );
        }

        //check if the user is registered or not.
        /*if( !$touranment->isRegistered($request->user()->id))
        {
            return $this->jsonResponseFail(
                trans('tournament/latearraival.create.notregistered')
            );
        }*/
        if(!$touranment->isLate($request->user()->id)){
            $touranment->latePlayers()->create(
                [
                    "user_id"=>$request->user()->id,
                    "latetime"=>$request->latetime
                ]
            );
			$touranment->registration_log()->create([
                'user_id'=>$request->user()->id,
                'status_from'=>0,
                'status_to'=>4,
				'added_by'=>$request->user()->roles->pluck('name')[0]
            ]);
			return $this->jsonResponseSuccess(
				trans('tournament/latearraival.create.success')
			);
        }else{
            $touranment->latePlayers()->where(['user_id'=>$request->user()->id])->update(
                ['latetime' => $request->latetime]
            );
			$touranment->registration_log()->create([
                'user_id'=>$request->user()->id,
                'status_from'=>4,
                'status_to'=>5,
				'added_by'=>$request->user()->roles->pluck('name')[0]
            ]);
			return $this->jsonResponseSuccess(
				trans('tournament/latearraival.update.success')
			);
		}
    }

    /**
     * @return TournamentResourceCollection
     */
    public function destroy(Request $request,$id)
    {
        $touranment = $this->touranment->show(
            ['id' => $id]
        );
        if (empty($touranment)) {
            return $this->jsonResponseFail(
                trans('tournament/latearraival.delete.fail')
            );
        }
        $touranment->latePlayers()->where(['user_id'=>$request->user()->id])->delete();
		$touranment->registration_log()->create([
                'user_id'=>$request->user()->id,
                'status_from'=>0,
                'status_to'=>6,
				'added_by'=>$request->user()->roles->pluck('name')[0]
            ]);
        return $this->jsonResponseSuccess(
            trans('tournament/latearraival.delete.success')
        );
    }

}
