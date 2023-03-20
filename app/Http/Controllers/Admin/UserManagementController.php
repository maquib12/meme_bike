<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
use App\Models\UserSubscription;
use App\Models\RideRoutes;
use App\Models\UserRide;
use App\Models\EnvironmentType;
use Validator;
use Auth;
use Session;
use Cache;
use Toastr;
use Helper;
use Image;
use Hash;
use Input;
use Config;
use Carbon\Carbon;

class UserManagementController extends Controller
{
    public function userManagement(){
        $today_date = date('Y-m-d');
        $users= User::leftjoin('user_subscriptions','user_subscriptions.user_id','=', 'users.id')
                      ->select('users.*','user_subscriptions.status as subscription_status','user_subscriptions.subscription_end_date')
                        ->where('user_type_id',Config::get('constants.user_type.user'))
                        ->orderBy('users.id','DESC')
                        ->get();

                        // $mytime = Carbon::now();
                        // $end_date = $users->subscription_end_date;
                        // $end= date('Y-m-d',strtotime($end_date));
        
                        // if(strtotime($end_date) < strtotime(date('Y-m-d'))){
            
                        //     $user= User::where('id',$request->input('id'))->update('status');
                        //      $userSubscription = UserSubscription::where('user_id',$request->input('id'))->update('status');
                        // }
        return view('admin.user-management',compact('users','today_date'));
    }

    public function viewUser(Request $request,$id){
        $user= User::leftjoin('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
                        ->select('users.id','users.first_name','users.last_name','users.email','users.status','user_subscriptions.subscription_start_date','user_subscriptions.subscription_end_date','user_subscriptions.status as subscription_status')
                        ->where(['user_type_id'=>Config::get('constants.user_type.user'),'users.id'=>$id])
                        ->orderBy('users.id','DESC')
                        ->first();

        $mytime = Carbon::now();
        $end_date = $user->subscription_end_date;
        $end= date('Y-m-d',strtotime($end_date));
        $diff_in_days = $mytime->diffInDays($end);

        if(strtotime($end_date) < strtotime(date('Y-m-d'))){
            $diff_in_days = 0;
            User::where('id',$id)->update(['status'=>'0']);
            UserSubscription::where('user_id',$id)->update(['status'=>'2']);

        }
        else{
            $diff_in_days = $diff_in_days + 1;
        }

    
        $userDetails = EnvironmentType::leftjoin('ride_routes','ride_routes.environment_type_id', '=','environment_types.id')
                                        ->leftjoin('user_rides','user_rides.ride_route_id', '=', 'ride_routes.id')
                                        ->leftjoin('users','users.id', '=', 'user_rides.user_id')
                                        ->select('last_update_date','start_from_location','end_from_location','distance','total_time',
                                    'solo_or_group_trip')
                                    ->where('users.id',$id)
                                    ->get();
        return view('admin.view-user',compact('user','userDetails','diff_in_days'));
                       
                       

        
    }
    public function status(Request $request)
    {

        
        $user= User::where('id',$request->input('id'))->update(['status'=>$request->input('status')]);
        $userSubscription = UserSubscription::where('user_id',$request->input('id'))->update(['status'=>$request->input('status')]);
        
        $message = array('message' => '', 'title' => 'Status Changed Successfully');
          return response()->json($message);
    }
}
