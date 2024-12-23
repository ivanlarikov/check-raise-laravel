<?php

namespace App\Http\Controllers\User\Room;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\Room\RoomStatisticsTournamentResourceCollection;
use App\Models\Room\Room;
use App\Http\Resources\User\Room\RoomStatisticsResource;
use App\Models\Tournament\Tournament;
use App\Traits\Response\ResponseTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomStatisticsController extends Controller
{
  use ResponseTrait;

  /**
   * @param Request $request
   * @return RoomStatisticsResource
   */
  public function index(Request $request): RoomStatisticsResource
  {
    $filters = $this->getFilters($request);

    $query = Room::with([
      'tournaments' => function ($q) use ($filters) {
        $q->withCount(
          [
            'registeredPlayers',
            'checkinPlayers',
            'rebuycount as reentry_sum' => function ($rebuyQuery) {
              $rebuyQuery->select(DB::raw("SUM(rebuycount) as reentrySum"));
            }
          ]
        )
          ->with(['rebuycount'])
          ->whereHas('detail', function ($detailQuery) use ($filters) {
            if ($filters['min_buy_in'] > 0 || $filters['max_buy_in'] < 300) {
              $detailQuery = $detailQuery->whereBetween('buyin', [$filters['min_buy_in'], $filters['max_buy_in']]);
            }
            $detailQuery->whereBetween('startday', [$filters['start_date'], $filters['end_date']]);
          });
      }
    ]);

    $query = $query->where('user_id', '=', $request->user()->id);
    $room = $query->first();
    return RoomStatisticsResource::make($room);
  }

  /**
   * @param Request $request
   * @return RoomStatisticsTournamentResourceCollection
   */
  public function tournamentList(Request $request): RoomStatisticsTournamentResourceCollection
  {
    $filters = $this->getFilters($request);
    $tournaments = Tournament::where('room_id', '=', $request->user()->room->id)
      ->whereHas('detail', function ($q) use ($filters) {
        $q->whereBetween('startday', [$filters['start_date'], $filters['end_date']]);
      })
      ->get();

    return RoomStatisticsTournamentResourceCollection::make($tournaments);
  }

  /**
   * Validation filters from request.
   *
   * @param Request $request
   * @return array
   */
  private function getFilters(Request $request): array
  {
    $filters = $request->all();

    // default start date.
    $defaultStartDate = (new Carbon('2024-04-01'))->setHour(0)->setMinute(0);
    // the default end date is yesterday.
    $defaultEndDate = Carbon::now()->subDay()->setHour(23)->setMinute(59);

    if (empty($filters)) {
      return [
        'start_date' => $defaultStartDate,
        'end_date' => $defaultEndDate,
        'min_buy_in' => 0,
        'max_buy_in' => 300,
      ];
    }

    $startDate = isset($filters['start_date']) && $filters['start_date'] !== 'null'
      ? (new Carbon($filters['start_date']))->setHour(0)->setMinute(0)
      : $defaultStartDate;
    $endDate = isset($filters['end_date']) && $filters['end_date'] !== 'null'
      ? (new Carbon($filters['end_date']))->setHour(23)->setMinute(59)
      : $defaultEndDate;

    $startDate = min(max($startDate, $defaultStartDate), $defaultEndDate);
    $endDate = max(min($endDate, $defaultEndDate), $defaultStartDate);

//    if ($startDate === $endDate) {
//      if ($startDate === $defaultStartDate) {
//        $endDate = (new Carbon($endDate))->addDay();
//      } else {
//        $startDate = (new Carbon($startDate))->subDay();
//      }
//    }

    $minBuyIn = min(max(0, $filters['min_buy_in']), $filters['max_buy_in']);
    $maxBuyIn = max(min(300, $filters['max_buy_in']), $minBuyIn);

    return [
      'start_date' => $startDate,
      'end_date' => $endDate,
      'min_buy_in' => $minBuyIn,
      'max_buy_in' => $maxBuyIn,
    ];
  }
}
