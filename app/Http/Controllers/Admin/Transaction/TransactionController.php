<?php

namespace App\Http\Controllers\Admin\Transaction;
use App\Http\Controllers\Controller;
use App\Traits\Response\ResponseTrait;
use App\Models\Room\Credit\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use ResponseTrait;
    
    /**
     * @param SettingResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
		 $transactions=DB::table('transactions')
            ->select('transactions.*','rooms.title')
            ->join('rooms', 'rooms.id', '=', 'transactions.room_id')->get();
        if($transactions){
            $res=array(
                'status'=>true,
                'data'=>$transactions
            );
            return json_encode($res);
        }
        $res=array(
            'status'=>false,
            'data'=>''
        );
        return json_encode($res);
    }
	public function edit($id){
		$data=Transaction::find($id);
		if($data){
            $res=array(
                'status'=>true,
                'data'=>$data
            );
            return json_encode($res);
        }
        $res=array(
            'status'=>false,
            'data'=>''
        );
        return json_encode($res);
	}
    public function update(Request $request,$id)
    {
        $data=Transaction::find($id);
        $data->description= $request->description;
		$data->amount= $request->amount;
		if($data->save()){
            $res=array(
                'status'=>true,
                'msg'=>"Transaction Updated!!!"
            );
            return json_encode($res);
		}
		$res=array(
            'status'=>false,
            'msg'=>"Transaction Not Updated!!!"
         );
		return json_encode($res);
            
    }
	public function destroy($id){
        $transactions = Transaction::where('id', "=", $id)->delete();
        if($transactions){
			$response=array(
				'status'=>true,
				'message'=>'Transactions Deleted Successfully!!!'
			);
		}else{
			$response=array(
				'status'=>true,
				'message'=>'Transactions Not Deleted!!!'
			);
		}
		return json_encode($response);
    }
    
}
