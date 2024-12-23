<?php

namespace App\Console;

use App\Models\Room\Room;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
  /**
   * Define the application's command schedule.
   */
  protected function schedule(Schedule $schedule): void
  {
    /**
     * Unpublish finished tournaments daily.
     */
    $schedule->call(function () {
      $today = new \DateTime();
      $today->setTime(8, 0, 0);

      DB::table('tournaments')
        ->leftJoin('tournament_details', 'tournament_details.tournament_id', '=', 'tournaments.id')
        ->where('tournaments.status', '=', 1)
        ->where('tournament_details.startday', '<=', $today->format('Y-m-d H:i:s'))
        ->update(['status' => 0]);
    })->dailyAt('08:00');

    /**
     * RM weekly report.
     */
    $schedule->call(function () {
      $this->sendRoomReport('weekly_report');
    })->weeklyOn(1, '8:05')->timezone('Europe/Berlin');

    /**
     * RM monthly report.
     */
    $schedule->call(function () {
      $this->sendRoomReport('monthly_report');
    })->monthlyOn(1, '8:05')->timezone('Europe/Berlin');
  }

  /**
   * Register the commands for the application.
   */
  protected function commands(): void
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }

  /**
   * @param $type string weekly_report or monthly_report
   * @return void
   */
  protected function sendRoomReport(string $type): void
  {
    if ($type === 'weekly_report') {
      $dt = Carbon::now()->subWeek();
      $startDate = $dt->startOfWeek()->format('Y-m-d H:i:s');
      $endDate = $dt->endOfWeek()->format('Y-m-d H:i:s');
    } else {
      $dt = Carbon::now()->subMonth();
      $startDate = $dt->startOfMonth()->format('Y-m-d H:i:s');
      $endDate = $dt->endOfMonth()->format('Y-m-d H:i:s');
    }

    $rooms = Room::query()
      ->without('detail')
      ->with([
        'tournaments' => function ($q) use ($startDate, $endDate) {
          $q->withCount([
            'rebuycount as reentry_sum' => function ($rebuyQuery) use ($startDate, $endDate) {
              $rebuyQuery->select(\DB::raw("SUM(rebuycount) as reentrySum"));
              $rebuyQuery->whereBetween('created_at', [$startDate, $endDate]);
            }
          ])
            ->with([
              'checkinPlayers' => function ($checkinQuery) use ($startDate, $endDate) {
                $checkinQuery->wherePivotBetween('created_at', [$startDate, $endDate]);
              }
            ])
            ->whereHas('detail', function ($detailQuery) use ($startDate, $endDate) {
              $detailQuery->whereBetween('startday', [$startDate, $endDate]);
            });
        }
      ])->get();

    foreach ($rooms as $room) {
      $tournamentCount = $room->tournaments->count();
      $totalRegisteredPlayerCount = 0;
      $totalCheckinPlayerCount = 0;
      $totalReentriesCount = 0;
      $totalPrizePool = 0;
      $cumulatedRakes = 0;

      foreach ($room->tournaments as $tournament) {
        $detail = $tournament->detail;
        $registeredCount = $tournament->registeredPlayers->count();
        $checkinPlayersCount = $tournament->checkinPlayers->count();
        $reentriesCount = $tournament->reentry_sum;

        $totalRegisteredPlayerCount += $registeredCount;
        $totalCheckinPlayerCount += $checkinPlayersCount;
        $totalReentriesCount += $reentriesCount;
        $totalPrizePool += (($detail->buyin + $detail->bounty) * $checkinPlayersCount) + (($detail->reentry + $detail->reentry_bounty) * $reentriesCount);
        $cumulatedRakes += $detail->rake * $checkinPlayersCount + $detail->reentriesrake * $reentriesCount;
      }

      sendEmail(
        'manager',
        $type,
        $room->id,
        [
          'tournaments' => $tournamentCount,
          'registered_players' => $totalRegisteredPlayerCount,
          'players_without_check_in' => $totalRegisteredPlayerCount - $totalCheckinPlayerCount,
          'players_without_check_in_percentage' => $this->calculatePercentage($totalRegisteredPlayerCount, $totalRegisteredPlayerCount - $totalCheckinPlayerCount),
          'players_with_check_in' => $totalCheckinPlayerCount,
          'players_with_check_in_percentage' => $this->calculatePercentage($totalRegisteredPlayerCount, $totalCheckinPlayerCount),
          're_entries' => $totalReentriesCount,
          're_entries_percentage' => $this->calculatePercentage($totalCheckinPlayerCount, $totalReentriesCount),
          'cumulated_prize_pools' => $totalPrizePool,
          'cumulated_rakes' => $cumulatedRakes,
        ]
      );
    }
  }

  private function calculatePercentage($total, $numerator): int|string
  {
    $percentage = 0;

    if ($total > 0 && $numerator > 0) {
      $percentage = number_format((($numerator / $total) * 100), 2);
    }

    return $percentage;
  }
}
