<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use Validator;
use Auth;
use Session;
use Cache;
use Config;
use Toastr;
use Carbon\Carbon;
use Image;
use Input;

class AdvertisementController extends Controller
{
     //Advertisement Management
     public function advertiseManagement(){
         $advertisement = Advertisement::orderby('id','DESC')->get();
        return view('admin.advertisement-management',compact('advertisement'));
    }

    public function addDetails(){
        return view('admin.add-details');
    }

    public function viewDetails(Request $request,$id){

        $viewDetails = Advertisement::leftjoin('advertisement_locations', 'advertisement_locations.advertisement_id', '=', 'advertisements.id')
                                      ->select('advertisements.*','advertisement_locations.address')
                                      ->where('advertisements.id',$id)
                                      ->where('status',1)
                                    ->first();
             
            $mytime = Carbon::now();
            $end_date = $viewDetails->validate_to;
            $end= date('Y-m-d',strtotime($end_date));
            $diff_in_days = $mytime->diffInDays($end);

            if(strtotime($end_date) < strtotime(date('Y-m-d'))){
                $diff_in_days = 0;
            }
            else{
                $diff_in_days = $diff_in_days + 1;
            }
        $advertisement1 = AdvertisementLocation::where('advertisement_id',$id)
                                        ->get();
        return view('admin.view-details',compact('viewDetails','advertisement1','diff_in_days'));
    }

    public function editDetails(Request $request,$id){
        if($request->isMethod('post'))
        {
            $validator = Validator::make($request->all(), [
                'advertise' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'location' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->messages();
                foreach ($messages->all() as $message)
                {
                    Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }else{
                $data=$request->all();

                //dd($data);
            

                $advertise=Advertisement::find($id);
                $advertise->advertise_name=$data['advertise'];
                $advertise->validate_from=$data['start_date'];
                $advertise->validate_to=$data['end_date'];
                if($request->hasFile('image'))
                    {
                        $image_tmp =$request->file('image');
                        if($image_tmp->isValid())
                        {
                        $extension =$image_tmp->getClientOriginalExtension();
                        $filename =rand(111,99999).'.'.$extension;
                        $image_path = 'uploads/'.$filename;
                        Image::make($image_tmp)->save($image_path);                          
                        $advertise->advertisement_image=$filename;
                            
                    
                    }
                    }
                    else
                    {
                        $advertise->advertisement_image=$data['current_image'];

                    }
            
                    $advertise->save();
                    Advertisement::where('id',$id)->update(['advertise_name'=>$data['advertise'],'validate_from'=>$data['start_date'],'validate_to'=>$data['end_date'],]);
                    AdvertisementLocation::where(['advertisement_id'=>$id])->delete();

                    foreach($data['location'] as $loc ){
                    // $ids =  array_search($loc,$data['location']); 

                    $advertise = new AdvertisementLocation;
                    $advertise->advertisement_id= $id;
                    $advertise->address= $loc;
                    $advertise->save();

                    }
                    Toastr::success('Advertisement Updated Successfully ','Success');
                    Toastr::clear();
                    return redirect()->route('advertise-management');

            }
                
        }

        $edit_details=Advertisement::find($id)
                        ->where('advertisements.id',$id)
                        ->where('status',1)
                        ->first();
        $edit_address=AdvertisementLocation::select('*')
                        ->where('advertisement_locations.advertisement_id',$id)
                        ->get();

        return view('admin.edit-details',compact('edit_details','edit_address'));
    }



    public function addDetailsPost(Request $request){
              $validator = Validator::make($request->all(), [
                'image'=> 'required|image|mimes:jpeg,png,jpg|max:2048',
                'title' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'location' => 'required',
            ]);

            if ($validator->fails()) {
                $messages = $validator->messages();
                foreach ($messages->all() as $message)
                {
                    Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            } else {
                    $data=$request->all();
                    $advertiseAdd=new Advertisement();
                    if($request->hasFile('image'))
                    {
                        $image_tmp =$request->file('image');
                        if($image_tmp->isValid())
                        {
                            $extension =$image_tmp->getClientOriginalExtension();
                            $filename =rand(111,99999).'.'.$extension;
                            $image_path = 'uploads/'.$filename;
                            Image::make($image_tmp)->save($image_path);                          
                            $advertiseAdd->advertisement_image=$filename;
                        }
                    }

                    $advertiseAdd->advertise_name=$data['title'];
                    $advertiseAdd->validate_from=$data['start_date'];
                    $advertiseAdd->validate_to=$data['end_date'];
                    $advertiseAdd->save();
                    
                    foreach($data['location'] as $loc){
                        $advertiseLocation=new AdvertisementLocation();
                        $advertiseLocation->address=$loc;
                       
                        $advertiseLocation->advertisement_id=$advertiseAdd->id;
                        $advertiseLocation->save();
                        
                    }
                 
                    $userId = $advertiseAdd->id;
                    
                    if($userId){
                        Toastr::success("advertise Added Successfully.", $title = "Success", $options = [] );
                        Toastr::clear();
                    } else {
                        Toastr::error("Something Went Wrong please try again.", 'Failed', ['options']);
                        Toastr::clear();
                    }
                    return redirect('/advertise-management');

           
             }
                
    }
    public function deleteLocation(Request $request,$id){
        $delete = AdvertisementLocation::where('id',$id)->delete();
        Toastr::success('Location deleted  Successfully ','Success');
        return redirect()->back();

    }
    public function deleteDetails(Request $request)
    {

       
         Advertisement::where('id',$request->advert_id)->delete();
         AdvertisementLocation::where('advertisement_id',$request->advert_id)->delete();
         Toastr::success('Advertisement deleted  Successfully ','Success');
         return redirect()->back();

    }
    
}

