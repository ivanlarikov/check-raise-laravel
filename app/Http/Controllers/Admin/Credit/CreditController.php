<?php

namespace App\Http\Controllers\Admin\Credit;
use App\Http\Controllers\Controller;
use App\Traits\Response\ResponseTrait;
use App\Models\Common\Credit;
use Illuminate\Http\Request;

class CreditController extends Controller
{
    use ResponseTrait;
    
    /**
     * @param SettingResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        $credits = Credit::all();
        if($credits){
            $res=array(
                'status'=>true,
                'data'=>$credits
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
        $data=Credit::find($id);
        $data->perday= $request->perday;
		$data->discount= $request->discount;
		if($data->save()){
            $res=array(
                'status'=>true,
                'msg'=>"Credits Updated!!!"
            );
            return json_encode($res);
		}
		$res=array(
            'status'=>false,
            'msg'=>"Credits Not Updated!!!"
         );
		return json_encode($res);
            
    }

    
}
