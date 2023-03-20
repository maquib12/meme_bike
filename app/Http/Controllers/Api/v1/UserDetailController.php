<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Friend;
use App\Models\StaticContent;
use App\Models\Subscription;
use App\Models\UserRide;
use App\Models\InviteFriend;
use App\Models\Character;
use App\Models\RideRoute;
use App\Models\EnvironmentType;
use App\Models\Weather;
use App\Models\Bike;
use Validator;
use Config;
use Auth;
use Image;
use Carbon\Carbon;

/*Note:trip_id is ride_route_id*/

class UserDetailController extends Controller
{

 public function getTermesAndConditions(Request $request){
     try{
     
       
         $data = StaticContent::where(["content_type"=>Config::get('constants.content_type.termsconditions'),"status"=>1])->select("id","title","description")->first();
         
         
          return response()->json(['status' => true,"message"=>"terms & conditions page fetch successfully.","data"=>$data,"code"=> 200],200);
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


       public function getAboutUs(Request $request){
        try{
     
        
          $data = StaticContent::where(["content_type"=>Config::get('constants.content_type.aboutus'),"status"=>1])->select("id","title","description")->first();
       
          return response()->json(['status' => true,"message"=>"About us page fetch successfully.","data"=>$data,"code"=> 200],200);
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }

      public function getFriendsList(Request $request){
        try{

         
          /*check auth*/
          if(!auth('api')->check()){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
        /*fetch friends data friends request get*/
         
        $friends= User::leftjoin("friends as f","f.friend_user_id","=","users.id")
        ->leftjoin("friends as fr","fr.user_id","=","users.id")
        ->where(["f.user_id"=>auth("api")->user()->id,
          "f.status"=>Config::get('constants.friend_request_status.friend')])
        ->orWhere(function($q2){
             $q2->where("fr.friend_user_id",auth("api")->user()->id)->where("fr.status",Config::get('constants.friend_request_status.friend'));
             })
        // ->orWhere(["fr.friend_user_id"=>auth("api")->user()->id,"fr.status"=>Config::get('constants.friend_request_status.friend')])
        ->select("users.id","users.username","users.profile_pic","users.first_name","users.last_name")->distinct()
        ->get(); 


           $friends_data=[];
            /*check object empty or not */
          if($friends->count()){

          foreach ($friends as $key => $friend) {

            $friendsData['user_id']=$friend->id;
            $friendsData['user_name']=$friend->username;
            $friendsData['first_name']=$friend->first_name;
            $friendsData['last_name']=$friend->last_name;
            if(!empty($friend->profile_pic)){
              $friendsData['profile_pic']=url("images")."/".$friend->profile_pic;
            }
            else{
              $friendsData['profile_pic']="NAN";
             }
            $friends_data[$key]=$friendsData;
           }
         }

          return response()->json(['status' => true,"message"=>"Friends data fetch successfully.","data"=>$friends_data,"code"=> 200],200);
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


        public function getPendingFriendRequestList(){
        try{

         
          /*check auth*/
          if(!auth("api")->check()){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
        /*fetch pending friends data*/
           
        $friends= User::leftjoin("friends as f","f.user_id","=","users.id")->where(["f.friend_user_id"=>auth("api")->user()->id,"f.status"=>Config::get('constants.friend_request_status.request')])->select("users.id","users.username","users.profile_pic","users.first_name","users.last_name")->get();

           $friends_data=[];
           /*check object empty or not */
          if($friends->count()){

          foreach ($friends as $key => $friend) {

            $friendsData['user_id']=$friend->id;
            $friendsData['user_name']=$friend->username;
            $friendsData['first_name']=$friend->first_name;
            $friendsData['last_name']=$friend->last_name;
            if(!empty($friend->profile_pic)){
              $friendsData['profile_pic']=url("images")."/".$friend->profile_pic;
            }
            else{
              $friendsData['profile_pic']="NAN";
            }
            $friends_data[$key]=$friendsData;
           }
         }

          return response()->json(['status' => true,"message"=>"Pending Friend list successfully.","data"=>$friends_data,"code"=> 200],200);
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }

      /*Unfriend API*/

       public function unfriendUser(Request $request){
        try{

          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'user_id_to_unfriend'=>'required|string'
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
            );           
        }
          /*check auth*/
          if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
        /*fetch Friends data*/
           
        $friends= Friend::where(["user_id"=>strip_tags(trim($request->user_id)),"friend_user_id"=>strip_tags(trim($request->user_id_to_unfriend))])->update(['status'=>Config::get('constants.friend_request_status.unfriend')]);

          
          if($friends){
            return response()->json(['status' => true,"message"=>"Unfriend successfully.","code"=> 200],200);
          }
          
           return response()->json(['status' => true,"message"=>"Fail to update.","code"=> 400],200);
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


       public function friendRequestSend(Request $request){
        try{

          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'send_request_to_user_id'=>'required|string'
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
            );           
        }
          /*check auth*/
          if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
        /*fetch user_id*/
         $user_id=User::where(["id"=>strip_tags(trim($request->send_request_to_user_id)),"status"=>1])->pluck("id")->first();

         if(empty($user_id)){
          return response()->json(['status' => false,"message"=>"Friend does not exists.","code"=> 400],200);
         }
         
         
          $friend= Friend::where(['friend_user_id'=>strip_tags(trim($request->send_request_to_user_id)),"user_id"=>trim($request->user_id)])->first();
             
          if(count((array)$friend)>0){
            $friend->status=2;
            if($friend->save()){
              return response()->json(['status' => true,"message"=>"Friends request send successfully.","code"=> 200],200);
            }
          }
          else{
              $friend= new Friend;
              $friend->user_id =strip_tags(trim($request->user_id)); 
              $friend->friend_user_id = strip_tags(trim($request->send_request_to_user_id));
              $friend->status=2;
              if($friend->save()){
                   return response()->json(['status' => true,"message"=>"Friends request send successfully.","code"=> 200],200);
              }
          }
          
          
           return response()->json(['status' => true,"message"=>"Fail to send friend request.","code"=> 400],200);
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


        public function searchUser(Request $request){
        try{

          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'search'=>'required|string'
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
            );           
        }
          /*check auth*/
          if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
       
         
          $search=strip_tags(trim($request->search)); 
          
          $users= User::whereRaw("username Like '%$search%'")
             ->where("id","!=",auth("api")->user()->id)
             ->orWhereRaw("first_name Like '%$search%'")
             ->orWhereRaw("last_name Like '%$search%'")  
             ->orWhereRaw("concat(first_name, ' ', last_name) like '%$search%' ")
             ->orderBy("first_name")
             ->get(); 
          
          /*check object empty or not */ 
            $user_data=[];
          if($users->count()){
          foreach ($users as $key => $user) {
            $userData['user_id']=$user->id;
            $userData['user_name']=$user->username;
            $userData['first_name']=$user->first_name;
            $userData['last_name']=$user->last_name;
            if(!empty($user->profile_pic)){
              $userData['profile_pic']=url("images")."/".$user->profile_pic;
            }
            else{
              $userData['profile_pic']="NAN";
            }
            $user_data[$key]=$userData;
           }
         
              return response()->json(['status' => true,"message"=>"user data fetch successfully.",'data'=>$user_data,"code"=> 200],200);
            }

             return response()->json(['status' => false,"message"=>"No Records Found.","code"=> 400],200);
          }
         
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


       public function getUserProfile(){
        try{

         
          /*check auth*/
          if(!auth("api")->check()){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }
       
         
          $user_id=auth("api")->user()->id; 
          
          $user= User::where('id',$user_id)
                ->select("id","username","first_name","last_name","profile_pic","date_of_birth","height","weight","gender","country")
                ->first();

          $total_friends= User::leftjoin("friends as f","f.friend_user_id","=","users.id")
        ->leftjoin("friends as fr","fr.user_id","=","users.id")
        ->where(["f.user_id"=>auth("api")->user()->id,
          "f.status"=>Config::get('constants.friend_request_status.friend')])
        ->orWhere(function($q2){
             $q2->where("fr.friend_user_id",auth("api")->user()->id)->where("fr.status",Config::get('constants.friend_request_status.friend'));
             })->distinct()->count("users.id"); 
        
          
          /*check object empty or not */ 
            $user_data=[];
          if(count((array)$user)>0){
            $userData['user_id']=$user->id;
            $userData['user_name']=$user->username;
            $userData['first_name']=$user->first_name;
            $userData['last_name']=$user->last_name;
            $userData['height']=$user->height;
            $userData['weight']=$user->weight;
            $userData['gender']=$user->gender;
            $userData['country']=$user->country;
            $userData['date_of_birth']=Carbon::parse($user->date_of_birth)->format('M d , Y');
            if(!empty($user->profile_pic)){
              $userData['profile_pic']=url("images")."/".$user->profile_pic;
            }
            else{
              $userData['profile_pic']="NAN";
            }
            $user_data['profile_data']=$userData;
            $totalFriend['total_friends']=$total_friends;
            $user_data['friends']=$totalFriend;

            /*fetch friend requests*/
              $friends= User::leftjoin("friends as f","f.user_id","=","users.id")
               ->where(["f.friend_user_id"=>$user_id,"f.status"=>Config::get('constants.friend_request_status.request')])
               ->select("users.id","users.username","users.profile_pic","users.first_name","users.last_name")
               ->get();

              if($friends->count()){
                $friend_request_data=[];
               foreach ($friends as $key => $friend) {
                  $userFriend['user_id']=$friend->id;
                  $userFriend['user_name']=$friend->username;
                  $userFriend['first_name']=$friend->first_name;
                  $userFriend['last_name']=$friend->last_name;
                  
                  if(!empty($friend->profile_pic)){
                    $userFriend['profile_pic']=url("images")."/".$friend->profile_pic;
                  }
                  else{
                    $userFriend['profile_pic']="NAN";
                  }
                  $friend_request_data[$key]=$userFriend;
                }
                 $user_data['friend_request']=$friend_request_data;
              }
              return response()->json(['status' => true,"message"=>"User Profile Fetch Successfully.",'data'=>$user_data,"code"=> 200],200);
            }

             return response()->json(['status' => false,"message"=>"No Records Found.","code"=> 400],200);
          }
         
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }



      public function updateProfile(Request $request){
        try{
             $user_id=strip_tags(trim($request->user_id));

          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'username'=>'required|string|unique:users,username,'.$user_id,
                'profile_pic'=>'sometimes|nullable|image',
                'date_of_birth'=>'required|date_format:Y-m-d',
                'country'=>'required|string',
                'gender'=>'required|string',
                'user_height'=>'required|numeric',
                'user_weight'=>'required|numeric'
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
            );           
        }
          /*check auth*/
          if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

             return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

          }

          
          $user = User::find($user_id);
          $user->username=trim($request->username);
          $user->country=trim($request->country);

          /*check profile pic uploaded or not*/
          if($request->hasFile('profile_pic')){

            $image_tmp =$request->file('profile_pic');
            $extension =$image_tmp->getClientOriginalExtension();
            $filename =rand(111,99999).'.'.$extension;
            $image_path = 'images/'.$filename;
            Image::make($image_tmp)->save($image_path);                          
            $user->profile_pic=$filename;

          }

          $user->date_of_birth=Carbon::parse(trim($request->date_of_birth))->format('Y-m-d');
          $user->height=trim($request->user_height);
          $user->weight=trim($request->user_weight);
          $user->gender=trim($request->gender);
          
          if($user->save()){
             return response()->json(['status' => true,"message"=>"User profile Updated Successfully.","code"=> 200],200);
          }
             return response()->json(['status' => false,"message"=>"Fail to update profile.","code"=> 400],200);
         }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        }
      }


      public function subscriptionPlans(){
        try{
           $subscriptions=Subscription::where("status",1)->orderBy('subscription_name')->select("id","subscription_name","subscription_period","subscription_price")->get();

           if($subscriptions->count()){
             return response()->json(['status'=>true,'message' =>"Subscription details fetch successfully","data"=>$subscriptions,'code' => 200],200);
           }
           else{
             return response()->json(['status'=>false,'message' =>"No records found",'code' => 400],200);
           }

        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        }
      }


      public function activeGroupTripList(){
        try{

          $id=auth("api")->user()->id;
    
          $groupTripLists=InviteFriend::leftjoin("user_rides as ur","ur.id","invite_friends.user_ride_id")
          ->leftjoin("users as u","u.id","ur.user_id")
          ->leftjoin("ride_routes as rr","rr.id","ur.ride_route_id")
          ->where(["invite_friends.friend_user_id"=>$id,"invite_friends.status"=>1,"u.status"=>1,"ur.status"=>1,"ur.solo_or_group_trip"=>0,"rr.status"=>1])
          ->select("u.profile_pic","u.username","u.first_name","u.last_name","rr.start_from_location","rr.end_from_location","rr.start_from_lat","rr.start_from_lng","rr.end_from_lat","rr.end_from_lng","u.id","rr.id as trip_id")
          ->get(); 

         if($groupTripLists->count()){
            $data=[];
            foreach ($groupTripLists as $key => $groupTripList) {

                  $groupTrip['user_id']=$groupTripList->id;
                  $groupTrip['trip_id']=$groupTripList->trip_id;
                  $groupTrip['user_name']=$groupTripList->username;
                  $groupTrip['first_name']=$groupTripList->first_name;
                  $groupTrip['last_name']=$groupTripList->last_name;
                  $groupTrip['start_from_location']=$groupTripList->start_from_location;
                  $groupTrip['end_from_location']=$groupTripList->end_from_location;
                  $groupTrip['start_from_lat']=$groupTripList->start_from_lat;
                  $groupTrip['start_from_lng']=$groupTripList->start_from_lng;
                  $groupTrip['end_from_lat']=$groupTripList->end_from_lat;
                  $groupTrip['end_from_lng']=$groupTripList->end_from_lng;

                  if(!empty($groupTripList->profile_pic)){
                    $groupTrip['profile_pic']=url("images")."/".$groupTripList->profile_pic;
                  }
                  else{
                    $groupTrip['profile_pic']="NAN";
                  }
                  $data[$key]=$groupTrip;
            }
           return response()->json(['status'=>true,'message' =>"Active Group Trip List successfully.",'data'=>$data,'code' => 200],200);
         }
         else{
          return response()->json(['status'=>false,'message' =>"No records found.",'code' => 400],200);
         }
        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        }
      }


        public function saveTrip(Request $request){

        try{
            
          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'character_id'=>'required|string',
                'bike_id'=>'required|string',
                'cruise_mode'=>'required|string',
                //'environment_type_id'=>'required|string',
                'start_from_location'=>'required|string',
                'start_from_lat'=>'required|string',
                'start_from_lng'=>'required|string',
                'end_from_location'=>'required|string',
                'end_from_lat'=>'required|string',
                'end_from_lng'=>'required|string',
                //'weather_type_id'=>'required|string',
                'invited_friends_ids'=>'sometimes|nullable|array',
                'start_trip_datetime'=>'required|string',
                 
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
                       );           
                   }


                   /*check auth*/
                if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

                   return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

                }

                $response_data=[];
                /*save ride routes data*/
                $ride_route=new RideRoute;

                $ride_route->user_id=trim($request->user_id);
                //$ride_route->environment_type_id=trim($request->environment_type_id);
                $ride_route->start_from_location=trim($request->start_from_location);
                $ride_route->start_from_lat=trim($request->start_from_lat);
                $ride_route->start_from_lng=trim($request->start_from_lng);
                $ride_route->end_from_location=trim($request->end_from_location);
                $ride_route->end_from_lat = trim($request->end_from_lat);
                $ride_route->end_from_lng = trim($request->end_from_lng);
                //$ride_route->weather_type_id = trim($request->weather_type_id);
                $ride_route->status     =Config::get('constants.ride_routes_status.active');
                
                $ride_route->save();
                                
               $response_data["trip_id"]=$ride_route->id;
               /*save user rides data*/
               $user_ride=new UserRide;
               $user_ride->user_id = trim($request->user_id);
               $user_ride->ride_route_id = $ride_route->id;
               $user_ride->distance = 0;
               $user_ride->average_speed = 0;
               $user_ride->total_time = 0;
               $user_ride->total_calories_burn = 0;
               $user_ride->start_trip_datetime       =  Carbon::parse(trim($request->start_trip_datetime))->format('Y-m-d H:i:s');
               $user_ride->cruise_mode=trim($request->cruise_mode);
                $user_ride->bike_id=trim($request->bike_id);
                $user_ride->character_id=trim($request->character_id);
               /*for group trip*/
               $tripType="solo";
               if(isset($request->invited_friends_ids)){
                $user_ride->solo_or_group_trip = 0;
                $tripType="group";
               }               
               else{/*for solo trip*/

                $user_ride->solo_or_group_trip = 1;
               }

               $response_data["trip_type"]=$tripType;

               $user_ride->status=Config::get('constants.user_rides_status.active');
               $user_ride->save();
              
                /*invite friends*/
               if(isset($request->invited_friends_ids)){
               $invite_friends  = $request->invited_friends_ids;
               foreach ($invite_friends as $key => $friends_id) {
               $inviteFriends                  =  new InviteFriend;
               $inviteFriends->user_ride_id    =  $user_ride->id;
               $inviteFriends->user_id         =  trim($request->user_id); 
               $inviteFriends->friend_user_id  =  $friends_id; 
               $inviteFriends->status          =  Config::get('constants.invite_friends_status.invite');
               $inviteFriends->save();
                  }

                }


            return response()->json(['message' =>"Trip created successfully.",
              'status'=>true,'data'=>$response_data,'code' => 201],200);
        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        }
      }



      public function joinTrip(Request $request){
        try
        {
          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'trip_id'=>'required|string',
                'character_id'=>'required|string',
                'bike_id'=>'required|string',
                'cruise_mode'=>'required|string',
                'start_trip_datetime'=>'required|string',
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
                       );           
                   }


                   /*check auth*/
                if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

                   return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

                }


                /*get trip data*/
               $trip_data = RideRoute::where(["id"=>trim($request->trip_id),"status"=>Config::get('constants.ride_routes_status.active')])->first();
                
                $user_ride_id=UserRide::where(["ride_route_id"=>trim($request->trip_id),"user_id"=>$trip_data->user_id])->first();
                
                /*check already joined or not */
                $already_joined=UserRide::where(["ride_route_id"=>trim($request->trip_id),"user_id"=>trim($request->user_id)])->first();

                if(count((array)$already_joined)>0){
                   return response()->json(['message' =>"Group trip already joined.",'status'=>true,'code' => 200],200);
                }


                if(count((array)$trip_data)>0){

                 /*save user rides data*/
                 $user_ride                            =  new UserRide;
                 $user_ride->user_id                   =  trim($request->user_id);
                 $user_ride->ride_route_id             =  $trip_data->id;
                 $user_ride->distance                  =  0;
                 $user_ride->average_speed             =  0;
                 $user_ride->total_time                =  0;
                 $user_ride->total_calories_burn       =  0;
                 $user_ride->solo_or_group_trip        =  0;
                 $user_ride->cruise_mode               =  trim($request->cruise_mode);
                 $user_ride->bike_id                   =  trim($request->bike_id);
                 $user_ride->character_id              =  trim($request->character_id);
                 $user_ride->status                    =  Config::get('constants.user_rides_status.active');
                 $user_ride->start_trip_datetime       =  Carbon::parse(trim($request->start_trip_datetime))->format('Y-m-d H:i:s');

                 if($user_ride->save()){
                  
                  $result=InviteFriend::where(["friend_user_id"=>trim($request->user_id),"user_ride_id"=>$user_ride_id->id])
                              ->update(["status"=>Config::get('constants.invite_friends_status.join_group')]);
                   
                   return response()->json(['message' =>"Group trip has been joined.",'status'=>true,'code' => 200],200);
                 }

                
               }

               else{
                  return response()->json(['message' =>"Trip does not exists.",'status'=>false,'code' => 400],200);
               }



        }
         catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        }

      }

     public function stopTrip(Request $request){
        
        try{

          $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'trip_id'=>'required|string',
                'trip_type'=>'required|string',
                'distance'=>'required|string',
                'remaning_distance'=>'required|string',
                'average_speed'=>'required|string',
                'end_trip_datetime'=>'required|string',
                'total_calories_burn'=>'required|string',
                'total_time'=>'required|string',
                
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
                       );           
                   }


                   /*check auth*/
                if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

                   return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

                }


                /*Get trip data for user who joined group
                   If solo then who created the group
                */
               $trip_data = UserRide::where(["ride_route_id"=>trim($request->trip_id),"status"=>Config::get('constants.user_rides_status.active'),"user_id"=>trim($request->user_id)])->first();
                 
                 /*check trip data exists*/
               if(count((array)$trip_data)>0){
                   $user_id=trim($request->user_id);
                   
                   /*
                   update user ride for the user who want stop ride
                   */
                   $result=UserRide::where([
                           "ride_route_id"=>trim($request->trip_id),
                            "user_id"=>$user_id])
                        ->update([
                          "distance"=>trim($request->distance),
                           "remaining_distance"=>trim($request->remaning_distance),
                           "end_trip_datetime"=>trim($request->end_trip_datetime),
                           "average_speed"=>trim($request->average_speed),
                           "total_time"=>trim($request->total_time),
                           "total_calories_burn"=>trim($request->total_calories_burn),
                           "status"=>Config::get('constants.user_rides_status.inactive'),
                           ]);

                         
                           /*Get trip data for user who created trip*/
                         $ride_route_data=RideRoute::where(["id"=>trim($request->trip_id)])->first();

                          /*Get ride details who want to stop either group creater or joiner*/
                          $user_ride_data=UserRide::where([
                         "ride_route_id"=>trim($request->trip_id),
                          "user_id"=>$user_id])->first();

                        
                        /*response data*/
                        $userride_data["distance"] = $user_ride_data->distance;
                        $userride_data["remaining_distance"] = $user_ride_data->remaining_distance;
                        $userride_data["total_time"] = $user_ride_data->total_time;
                        $userride_data["average_speed"] = $user_ride_data->average_speed;
                        $userride_data["total_calories_burn"] = $user_ride_data->total_calories_burn;
                        $userride_data["start_trip_datetime"] = $user_ride_data->start_trip_datetime;
                        $userride_data["end_trip_datetime"] = $user_ride_data->end_trip_datetime;
                        $userride_data["start_from_location"] = $ride_route_data->start_from_location;
                        $userride_data["start_from_lat"] = $ride_route_data->start_from_lat;
                        $userride_data["start_from_lng"] = $ride_route_data->start_from_lng;
                        $userride_data["end_from_location"] = $ride_route_data->end_from_location;
                        $userride_data["end_from_lat"] = $ride_route_data->end_from_lat;
                        $userride_data["end_from_lng"] = $ride_route_data->end_from_lng;
                        

                         /*for group trip */
                        if(trim($request->trip_type)=="0"){
                        if($result){
                          /*ride route details of trip*/
                          $ride_data=RideRoute::where("id",trim($request->trip_id))
                                        ->select("id","user_id")->first();
                          
                          /*trip creater ride route details*/
                          $user_ride_id=UserRide::where(["user_id"=>$ride_data->user_id,
                            "ride_route_id"=>$ride_data->id])->first();

                         /*if group joinner update status in invite friends*/
                          $result=InviteFriend::where([
                            "friend_user_id"=>$user_id,
                            "user_ride_id"=>$user_ride_id->id,
                            "user_id"  =>$ride_data->user_id
                          ])
                          ->update([
                            "status"=>Config::get('constants.invite_friends_status.leave_group')
                          ]);
                          
                          /*check all friends leave the trip or not*/
                          $checkFirendsLeaveTrip =InviteFriend::where([
                            "user_ride_id"=>$user_ride_id->id,
                            "user_id"  =>$ride_data->user_id,
                            "status"=>Config::get('constants.invite_friends_status.join_group')
                          ])->get();
                          
                          /*check trip creater already leaved*/

                          if($checkFirendsLeaveTrip->count()==0 && $ride_data->user_id==trim($request->user_id) ||$checkFirendsLeaveTrip->count()==0 && $user_ride_id->status==0){
                            
                          $ride_data=RideRoute::find(trim($request->trip_id));
                          /*update status*/
                          $ride_data->status  = Config::get('constants.ride_routes_status.inactive');
                          $ride_data->save();
                          }
                        }
                      }
                      /*for solo trip*/
                      else{
                        $ride_data=RideRoute::find(trim($request->trip_id));
                          /*update status*/
                          $ride_data->status  = Config::get('constants.ride_routes_status.inactive');
                          $ride_data->save();
                      }
                       

                      

                         return response()->json(['status'=>true,'message' =>"trip stop successfully.",'data'=>$userride_data,'code' => 200],200);

                     
                      
               }
                 
                return response()->json(['message' =>"User rides data not found.",'status'=>false,'code' => 400],200);


        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        } 

      }




      public function getTripDetails($trip_id){
        try{
            $exists=RideRoute::where("id",trim($trip_id))->first();

           if(count((array)$exists)>0){

            $response=[];
            $user_data=[];
            

           $ride_details=RideRoute::leftjoin("user_rides","user_rides.ride_route_id","ride_routes.id")
           ->where(["ride_routes.id"=>trim($trip_id),"user_rides.user_id"=>auth("api")->user()->id])
           ->select("ride_routes.*","user_rides.character_id","user_rides.bike_id",
            "user_rides.cruise_mode","user_rides.distance","user_rides.remaining_distance","user_rides.average_speed","user_rides.total_time","user_rides.total_calories_burn","user_rides.solo_or_group_trip","user_rides.id as ride_id","user_rides.start_trip_datetime","user_rides.end_trip_datetime")
           ->first();

           $user_data["total_calories_burn"]=$ride_details->total_calories_burn;
           $user_data["total_time"]=$ride_details->total_time;
           $user_data["cruise_mode"]=$ride_details->cruise_mode;
           $user_data["distance"]=$ride_details->distance;
           $user_data["remaining_distance"]=$ride_details->remaining_distance;
           $user_data["bike_id"]=$ride_details->bike_id;
           $user_data["character_id"]=$ride_details->character_id;
           $user_data["average_speed"]=$ride_details->average_speed;
           //$user_data["environment_type_id"]=$ride_details->environment_type_id;
           //$user_data["weather_type_id"]=$ride_details->weather_type_id;
           $user_data["start_from_location"]=$ride_details->start_from_location;
           $user_data["start_from_lat"]=$ride_details->start_from_lat;
           $user_data["start_from_lng"]=$ride_details->start_from_lng;
           $user_data["end_from_location"]=$ride_details->end_from_location;
           $user_data["end_from_lat"]=$ride_details->end_from_lat;
           $user_data["end_from_lng"]=$ride_details->end_from_lng;
           $user_data["start_trip_datetime"]=$ride_details->start_trip_datetime;
           $user_data["end_trip_datetime"]=$ride_details->end_trip_datetime;
           
           

            if($ride_details->solo_or_group_trip=="0"){

               $user_data["trip_type"]="group";
               $response["ride_details"]=$user_data;

               $friends=InviteFriend::leftjoin("users as u","u.id","invite_friends.friend_user_id")->where(["invite_friends.user_ride_id"=>$ride_details->ride_id,"invite_friends.status"=>
                Config::get('constants.invite_friends_status.leave_group'),
                "invite_friends.user_id"=>$ride_details->user_id])->select("u.profile_pic","u.first_name","u.last_name","u.username","u.id")->orderBy("u.first_name")->get();

             $friends_data=[];
            /*check object empty or not */
          if($friends->count()){

          foreach ($friends as $key => $friend) {

            $friendsData['user_id']=$friend->id;
            $friendsData['user_name']=$friend->username;
            $friendsData['first_name']=$friend->first_name;
            $friendsData['last_name']=$friend->last_name;
            if(!empty($friend->profile_pic)){
              $friendsData['profile_pic']=url("images")."/".$friend->profile_pic;
            }
            else{
              $friendsData['profile_pic']="NAN";
                }
            $friends_data[$key]=$friendsData;
             }
           $response["friends"] = $friends_data;
             }
          
            }
            else{
              $user_data["trip_type"]="solo";
              $response["ride_details"]=$user_data;
            }

            /*close friend*/
            return response()->json(['status'=>true,'message' =>"Trip details fetch successfully.","data"=>$response,'code' => 200],200);
           }
           else{
             return response()->json(['message' =>"Trip does not exists.",'status'=>false,'code' => 400],200);
           }
        }

        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        } 


      }


      public function getCharacter(){

        try{
                $characters=Character::where("status",1)->select("id","character_name","thumbnail")->orderBy("character_name")->get();

                if($characters->count()){

                   return response()->json(['status'=>true,'message' =>"Character Data fetch successfully","data"=>$characters,'code' => 200],200);
                }

                return response()->json(['status'=>false,'message' =>"No records found",'code' => 200],200);
        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        } 

      }


       public function getBikes(){

        try{
                $bikes=Bike::where("status",1)->select("id","bike_name","thumbnail")->orderBy("bike_name")->get();

                if($bikes->count()){

                   return response()->json(['status'=>true,'message' =>"Bike Data fetch successfully","data"=>$bikes,'code' => 200],200);
                }

                return response()->json(['status'=>false,'message' =>"No records found",'code' => 200],200);
        }
        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        } 

      }



       public function getAllTripList(Request $request){

        try{

           $validator = Validator::make($request->all(), [ 
                'user_id'=>'required|string',
                'trip_type'=>'sometimes|nullable|string',
                'start_date'=>'sometimes|nullable|date_format:Y-m-d',
                'end_date'=>'sometimes|nullable|date_format:Y-m-d',
                'offset'  =>'sometimes|nullable|numeric',
                'limit'  =>'sometimes|nullable|numeric',
                
            ]);
           
            if ($validator->fails()) { 
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            $result=$transformed; 
            return response()->json(
            [
                
                'status' => 400,
                'message' => 'Validation Error',
                'result' =>$result
                ],
                200
                       );           
                   }


                   /*check auth*/
                if(strip_tags(trim($request->user_id))!=auth("api")->user()->id){

                   return response()->json(['status' => false,"message"=>"Unauthorized User.","code"=> 400],200);

                }

                  $offset=trim($request->offset);
                  if(empty($skip)){
                    $skip=0;
                  }

                   $limit=trim($request->limit);
                  if(empty($limit)){
                    $limit=10;
                  }
              

               $fromDate='';
               if(!empty(trim($request->start_date))){
                $start_date= explode('-', trim($request->start_date));
               $fromDate=$start_date[2].'-'.$start_date[1].'-'.$start_date[0].' 00:00:00';
               }
               
               //end date
               $toDate='';
               if(!empty(trim($request->end_date))){
               $end_date= explode('-', trim($request->end_date));
               $toDate=$end_date[2].'-'.$end_date[1].'-'.$end_date[0].' 23:59:59';
                }
               
               $query=RideRoute::query();
               $query->leftjoin("user_rides as ur","ur.ride_route_id","ride_routes.id")
                         ->where("ur.user_id",trim($request->user_id));

                if(!empty($request->start_date)){
                 $query->whereDate('ur.start_trip_datetime','>=',$fromDate);

                  }
                 if(!empty($request->end_date)){
                 $query->whereDate('ur.start_trip_datetime','<=',$toDate);
                  }

                  if(!empty(trim($request->trip_type))){
                  $query->whereDate('ur.solo_or_group_trip',trim($request->trip_type));
                  }

                $tripLists= $query->select("ur.solo_or_group_trip","ur.distance","ur.remaining_distance","ur.average_speed","ur.total_time","ur.total_calories_burn","ride_routes.start_from_location","ride_routes.start_from_lat","ride_routes.start_from_lng","ride_routes.end_from_location","ride_routes.end_from_lat","ride_routes.end_from_lng","ride_routes.id as trip_id",\DB::raw('DATE_FORMAT(ur.start_trip_datetime, "%Y %M %d , %r") as start_trip_datetime'))
                       ->orderBy("ur.start_trip_datetime","desc")
                        ->skip(strip_tags(trim($offset)))
                        ->take(strip_tags(trim($limit)))
                        ->get();

                  
                 
                 if($tripLists->count()){

                  $trip_data=[];
                  $data     =[];

                  foreach ($tripLists as $key => $tripList) {

                   $trip_data["start_trip_datetime"]=$tripList->start_trip_datetime;
                    $trip="solo";

                   if($tripList->solo_or_group_trip==0){
                    $trip="group";
                   }
                   $trip_data["trip_id"]=$tripList->trip_id;
                   $trip_data["trip_type"]=$trip;
                   $trip_data["distance"]=$tripList->distance;
                   $trip_data["remaining_distance"]=$tripList->remaining_distance;
                   $trip_data["average_speed"]=$tripList->average_speed;
                   $trip_data["total_time"]=$tripList->total_time;
                   $trip_data["total_calories_burn"]=$tripList->total_calories_burn;
                   $trip_data["start_from_location"]=$tripList->start_from_location;
                   $trip_data["start_from_lat"]=$tripList->start_from_lat;
                   $trip_data["start_from_lng"]=$tripList->start_from_lng;
                   $trip_data["end_from_location"]=$tripList->end_from_location;
                   $trip_data["end_from_lat"]=$tripList->end_from_lat;
                   $trip_data["end_from_lng"]=$tripList->end_from_lng;
                   $trip_data["start_trip_datetime"]=$tripList->start_trip_datetime;

                   $data[$key]=$trip_data;
                  }



                  return response()->json(['status'=>true,'message' =>"Trips data fetch successfully.","data"=>$data,'code' => 200],200);
                 }
                 else{

                  return response()->json(['status'=>false,'message' =>"No records found.",'code' => 400],200);

                 }

              }

        catch(Exception $ex){
          return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 500],200);
        } 

      }

}
