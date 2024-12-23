<?php

namespace App\Http\Controllers\User\Room\Credit;

use App\Http\Controllers\Controller;
use App\Models\Common\Setting;
use App\Services\Room\Credit\TransactionService;
use App\Http\Resources\User\Room\Credit\TransactionResourceCollection;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Models\Common\Discount;
use App\Traits\Response\ResponseTrait;
use Illuminate\Http\Request;
use App\Models\Room\Room;

class TransactionController extends Controller
{
  use ResponseTrait;

  /**
   * @var TransactionService
   */
  protected TransactionService $transaction;


  /**
   * @param TransactionService $transaction
   */
  public function __construct(TransactionService $transaction)

  {
    $this->transaction = $transaction;
  }

  /**
   * @param Request $request
   * @return TransactionResourceCollection
   */
  public function index(Request $request): TransactionResourceCollection
  {
    //room_id get
    $room_id = $request->user()->room->id;
    return TransactionResourceCollection::make(
      $this->transaction->all(null, ['room_id' => $room_id], null, null, null, null, 1000, true),
    );
  }

  public function store(Request $request): JsonResponse
  {
    $roomId = $request->user()->room->id;
    $orderId = $request->input('order_id');
    $provider = new PayPalClient;

    //$transaction= new Transaction;
    $provider->setApiCredentials(config('paypal'));

    $paypalToken = $provider->getAccessToken();

    $orderDetails = $provider->showOrderDetails($orderId);

    if ($orderDetails) {
      $transaction = $this->transaction->show(
        ['paypalorderid' => $orderId]
      );

      if ($transaction) {
        //check if order id
        return $this->jsonResponseFail(
          trans('user/room/room.create.fail'),
          400
        );
      }
      $orderAmount = floatval($orderDetails['purchase_units'][0]['amount']['value']);
      $paypalFee = floatval(Setting::first()->paypal_fee);

      $orderAmount = round($orderAmount / (1 + $paypalFee / 100));

      $discount = Discount::where('extra_credit', '>', 0)
        ->where('start_credit', '<=', $orderAmount)
        ->where('end_credit', '>=', $orderAmount)
        ->first();

      if ($discount) {
        if ($discount->is_percentage == 0) {
          $orderAmount = $orderAmount + $discount->extra_credit;
        } else {
          $credit = ($orderAmount * $discount->extra_credit) / 100;
          $orderAmount = $orderAmount + $credit;
        }
      }
      $transaction = $this->transaction->create(
        [
          'room_id' => $roomId,
          'description' => 'Credit Purchase from Paypal',
          'amount' => $orderAmount,
          'paypalorderid' => $request->orderid
        ]
      );
      $room = Room::find($roomId);
      $room->credits = $room->credits + $orderAmount;
      $room->save();

      $transactionDate = Carbon::now()->format('d.m.Y H:i');
      sendEmail(
        'manager',
        'buy_credits',
        $roomId,
        [
          'amount' => $orderAmount,
          'date' => $transactionDate
        ]
      );
      sendEmail(
        'admin',
        'new_transaction',
        null,
        [
          'room_title' => $request->user()->room->title,
          'amount' => $orderAmount,
          'transaction_date' => $transactionDate,
          'id' => $transaction->id,
        ]
      );

      return $this->jsonResponseSuccess(
        trans('user/room/room.create.success')
      );

    } else {
      return $this->jsonResponseFail(
        trans('user/room/room.create.fail'),
        400
      );
    }
  }
}
