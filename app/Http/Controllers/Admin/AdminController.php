<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
use App\Models\Notification;
use Validator;
use Auth;
use Session;
use Cache;
use Config;
use Toastr;
use Carbon\Carbon;
use Image;
use Input;
use DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        if($request->isMethod('post')){
            $validator = Validator::make($request->all(),[

                'email' => 'required|email',
                'password' => 'required'
            ]);
        if ($validator->fails()) {
            $messages = $validator->messages();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Failed', ['timeOut' => 2000]);
            }
            return redirect()->back()
              ->withErrors($validator)
              ->withInput();
        }else{
                $email=$request->email;
                $password=$request->password;
            if (Auth::attempt(['email' => $email, 'password' => $password,'status'=>1])) {
                    Toastr::success('Login Successfully ','Success');
                    return redirect('/dashboard');
                } else {
                    Toastr::error('Please enter valid credentials.','Failed');
                    return redirect('/login');
                }
            }
        }

        // if($request->isMethod('post')){
        //     $validator = Validator::make($request->all(),[

        //         'email' => 'required|email',
        //         'password' => 'required|min:8|max:16'
        //     ]);
        // if ($validator->fails()) {
        //     $messages = $validator->messages();
        //     return redirect()->back()
        //       ->withErrors($validator)
        //       ->withInput();
        //       Toastr::error("Please enter valid credentials.", 'Failed', ['options']);
        // }
        //         $email=$request->email;
        //         $password=$request->password;
        //     if (Auth::attempt(['email' => $email, 'password' => $password,'status'=>1])) { 
        //           if ((auth::user()->user_type_id  == config::get('constants.user_type.admin'))) {
        //               return redirect()->route('dashboard');
        //             } else {
        //                 Toastr::error("You are not authorised to access.", 'Failed', ['options']);
        //                 Toastr::clear();
        //                 return redirect()->back();
        //             }
        //         } else {
        //             Toastr::error("Please enter valid credentials.", 'Failed', ['options']);
        //             Toastr::clear();
        //             return redirect()->back();
        //         }
        // }
        
        return view('admin.index');
    }


    //Edit Profile

    public function editProfile(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data=$request->all();
            $users_details=User::where(['user_type_id'=>Config::get('constants.user_type.admin')])->first();

            $user_id=$users_details->id;
            //dd($data);
         

            $user=User::find($user_id);
            $user->email=$data['email'];
            $user->first_name=$data['first_name'];
            $user->last_name=$data['last_name'];

            
            if($request->hasFile('image'))
            {
            $image_tmp =$request->file('image');
            if($image_tmp->isValid())
            {
            $extension =$image_tmp->getClientOriginalExtension();
            $filename =rand(111,99999).'.'.$extension;
            $image_path = 'images/'.$filename;
            Image::make($image_tmp)->save($image_path);                          
            $user->profile_pic=$filename;
                       
            
            }
            }
            else
            {
                $user->profile_pic=$data['current_image'];

            }
            $user->save();
            User::where('id',$user_id)->update(['first_name'=>$data['first_name'],'last_name'=>$data['last_name']]);
            Toastr::success('Profile Updated Successfully ','Success');
            return redirect('/dashboard');

        }
            $profile= User::select('*')
                            ->where('users.user_type_id',Config::get('constants.user_type.admin'))
                            ->first();
                return view('admin.edit-profile',compact('profile'));
    }

    //Logout
    public function logout(){

        
        Auth::logout();
        Session::flush();
        Cache::flush();
        Toastr::success('Logout Successfully','Success');
        return redirect()->route('login');
    }


//Dashboard
    public function dashboard(){
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $total = User::count();
        $totalUser = User::where('user_type_id',2)->count();
        $totalActiveUser = User::where('user_type_id',2)->where('status',1)->count();
        $totalInActiveUser = User::where('user_type_id',2)->where('status',0)->count();
        $newUser = User::where('user_type_id',2)
                        ->where('created_at','>=',Carbon::now()->subdays(15))
                        ->where('status',1)->count();

        $totalUserPercentage = ($totalUser/$totalUser) * 100;
        $percentageCount = number_format($totalUserPercentage,0);

        $percentageActive = number_format(($totalActiveUser/$totalUser) * 100,0);

        $percentageInActive = number_format(($totalInActiveUser/$totalUser) * 100,0);

        $percentageNewUser = number_format(($newUser/$totalUser) * 100, 0);

        $userMonth = array();
        for($i = 1; $i<=12; $i++){
            $a = 12 -$i;

           $total = User::select(\DB::raw("COUNT(*) as count"))
                               ->whereYear('created_at', date('Y'))
                               ->whereMonth('created_at', '=', $i)
                               ->where('user_type_id',2)
                               ->where('status',1)
                               ->pluck('count');
                               array_push($userMonth,$total);
        }

        $userWeek = array();
        
        for($i = 0; $i < 7; $i++){
            $y = Carbon::now()->subDay($i)->format('Y');
            $m = Carbon::now()->subDay($i)->format('m');
            $d = Carbon::now()->subDay($i)->format('d');
            $totalWeekUser = User::select(\DB::raw("COUNT(*) as count"))
                                
                            ->whereYear('created_at','=', $y)
                            ->whereMonth('created_at','=', $m)
                            ->whereDay('created_at','=', $d)
                            
                               ->where('user_type_id',2)
                               ->where('status',1)
                               ->pluck('count');

                
                            array_push($userWeek,$totalWeekUser);
        }
        $userWeek = array_reverse($userWeek);
        // dd($userWeek);


        //Yearly User Data
         $userYear = array();
         for($i = 0; $i< 10; $i++){
            $now = Carbon::now()->subYear($i)->format('Y');
             //dd($now);
            $totalUserYear = User::select(DB::raw("COUNT(*) as count"))
                           ->whereYear('created_at','=', $now)
                           ->where('user_type_id',2)
                            ->where('status',1)
                            ->pluck('count');
             array_push($userYear,$totalUserYear);
         }
         $userYear = array_reverse($userYear);

              //dd($userYear);

        return view('admin.dashboard',compact('totalUser','totalActiveUser',
        'totalInActiveUser','newUser','percentageCount','percentageActive',
        'percentageInActive','percentageNewUser','userMonth','userWeek','userYear'));
    }

   

//    public function monthChart(){
//     $userMonth = array();
//     for($i = 1; $i<=12; $i++){
//         $a = 12 -$i;

//        $total = User::select(\DB::raw("COUNT(*) as count"))
//                            ->whereYear('created_at', date('Y'))
//                            ->whereMonth('created_at', '=', $i)
//                            ->where('user_type_id',2)
//                            ->where('status',1)
//                            ->pluck('count');
//                            array_push($userMonth,$total);
//     }
//     return response()->json(['userMonth'=>$userMonth]);
//    }

public function notifications(){
    $JoinData = Notification::select('message','created_at','readBy')->where('type',1)->get();
    $newSubscriptionData = Notification::select('message','created_at','readBy')->where('type',2)->get();
    $read = Notification::where('readBy',2)->update(['readBy'=>1]);

    $userJoinData = array();
    $subcriptionData = array();
    foreach($JoinData as $key => $value){
        $userJoinData [$key]['message'] = $value->message;
        $userJoinData[$key]['readBy'] = $value->readBy;
   }
   foreach($newSubscriptionData as $key => $row){
      $subcriptionData [$key]['message'] = $row->message;
      $subcriptionData[$key]['readBy'] = $value->readBy;

   }
    
    return response()->json(['push'=>$userJoinData,'push1'=>$subcriptionData]);


}
public function notificationListing(){
    $notifi = Notification::leftjoin('users','users.id', '=', 'notifications.user_id')->select('users.id','email','message')->where('readBy',1)->where('user_id','!=','1')->get();
    return view('admin.notifications',compact('notifi'));
}

public function navbar(){
    $notifications = Notification::select('readBy')->where('readBy',2)->get()->count();
    return response()->json(['count'=>$notifications]);

}
}
