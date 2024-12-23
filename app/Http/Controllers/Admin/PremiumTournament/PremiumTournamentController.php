<?php

namespace App\Http\Controllers\Admin\PremiumTournament;

use App\Models\Common\PremiumTournament;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PremiumTournamentController
{
  use ResponseTrait;

  public function index()
  {
    $premiumTournaments = PremiumTournament::with(['room', 'tournament'])->get();
    return $this->jsonResponseSuccess(['data' => $premiumTournaments]);
  }

  public function show()
  {

  }

  public function update()
  {

  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function destroy(Request $request, $id): JsonResponse
  {
    PremiumTournament::destroy($id);
    return $this->jsonResponseSuccess();
  }
}
