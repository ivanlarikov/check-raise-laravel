<?php
namespace App\Http\Controllers\Admin\Discount;
use App\Http\Controllers\Controller;
use App\Traits\Response\ResponseTrait;
use App\Models\Common\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    use ResponseTrait;
    
    /**
     * @param SettingResourceCollection $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request){
        $discount = Discount::all();
        if($discount){
            $res=array(
                'status'=>true,
                'data'=>$discount
            );
            return json_encode($res);
        }
        $res=array(
            'status'=>false,
            'data'=>''
        );
        return json_encode($res);
    }
    public function store(Request $request){
		$data=$request->all();
		$discount =  new Discount;
		$discount->start_credit=$data['start_credit'];
		$discount->end_credit=$data['end_credit'];
		$discount->is_percentage=$data['is_percentage'];
		$discount->extra_credit=$data['extra_credit'];
		if($discount->save()){
			$response=array(
				'status'=>true,
				'message'=>'Discount Added Successfully!!!'
			);
		}else{
			$response=array(
				'status'=>true,
				'message'=>'Discount Not Added!!!'
			);
		}
		return json_encode($response);
    }
	public function edit($id){
		$discount = Discount::find($id);
        if($discount){
			$response=array(
				'status'=>true,
				'data'=>$discount
			);
		}else{
			$response=array(
				'status'=>true,
				'data'=>'No Discount Found!!!'
			);
		}
		return json_encode($response);
    }
    public function update(Request $request,$id)
    {
        $data=Discount::find($id);
        $data->start_credit= $request->start_credit;
		$data->end_credit= $request->end_credit;
		$data->is_percentage= $request->is_percentage;
		$data->extra_credit= $request->extra_credit;
		if($data->save()){
            $res=array(
                'status'=>true,
                'msg'=>"Discount Updated!!!"
            );
            return json_encode($res);
		}
		$res=array(
            'status'=>false,
            'msg'=>"Discount Not Updated!!!"
         );
		return json_encode($res);
            
    }
	public function destroy($id){
        $discount = Discount::where('id', "=", $id)->delete();
        if($discount){
			$response=array(
				'status'=>true,
				'message'=>'Discount Deleted Successfully!!!'
			);
		}else{
			$response=array(
				'status'=>true,
				'message'=>'Discount Not Deleted!!!'
			);
		}
		return json_encode($response);
    }
    
}
