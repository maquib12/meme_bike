<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Validator;
use Config;
use Auth;
use Helper;
use Carbon\Carbon;



class AuthController extends Controller
{

 public function login(Request $request){
     try{
      $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
       if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }
       
       
       $credentials = $request->only('username', 'password');
        if(!Auth::attempt($credentials)){
            return response()->json([
            	'status' =>false,
                'message' => 'Unauthorized'
            ], 200);
        }
        else if(Auth::attempt($credentials)){
          $user = User::where(["id"=>Auth::user()->id,"status"=>1,"is_otp_verified"=>1])->first();
          if(count((array)$user)==0){
          return response()->json(['status' => false,"message"=>"Either user is not verified or inactive.","code"=> 400],200);
          }
        }
         $user = User::find(Auth::user()->id);
         $token = $user->createToken($user->email.'-'.now());
         $data=['access_token' => $token->accessToken,"user_id"=>$user->id];
          return response()->json(['status' => true,"message"=>"Logged in successfully.","data"=>$data,"code"=> 200],200);
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }

    public function signup(Request $request){
     try{
      $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:6',
            'email_id' => 'required|string|unique:users,email',
            'country' => 'required|string',
        ]);
       if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }
       
         /* OTP */
         $digits = 5;
         $otp = rand(pow(10, $digits - 1) , pow(10, $digits) - 1);

         /*Save user data*/
         $user        = new User;
         $user->email = strip_tags(trim($request->email_id));
         $user->password = bcrypt(strip_tags(trim($request->password)));
         $user->username = strip_tags(trim($request->username));
         $user->status = 1;
         $user->otp = $otp;
         $user->user_type_id =Config::get('constants.user_type.user');
         $user->is_otp_verified=0;
         $user->country=strip_tags(trim($request->country));
         
         //*check if svae successfully*/
         if($user->save()){
            
             /* View */
            $view = 'send_otp';
            $subject = 'Otp';
             /* Data array */
            $data = ['otp' => $otp, 'user_name' => $user->username, 'email' => $user->email];
            /* Send Mail */
            $check = Helper::sendEmail($data, $view, $subject);
             
             if($check){

              /*user data*/
              $user_data=["user_id"=>$user->id];
              return response()->json([ "status"=>true,"message"=> "signup successfully.OTP sent to your email.","data"=>$user_data,"code"=> 201],200);
                }
         }
        
        
         }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
       
      }


       public function verifyOtp(Request $request){
       	try{
       $validator = Validator::make($request->all(), [
            'user_id' => 'required|string',
            'otp' => 'required|string',
        ]);

          if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }

        $user_id = trim($request->get('user_id'));
        $otp = trim($request->get('otp'));
         
        $user = User::where(['id'=>$user_id,"status"=>1])->first();
        /*if user exists*/
        if (count((array)$user)>0){
        if ($user->otp  == $otp) {
          $user->otp="";
          $user->is_otp_verified = 1;
          $user->email_verified_at = Carbon::now();
          $user->save();

          $token = $user->createToken($user->email.'-'.now());
          $data=['access_token' => $token->accessToken,"user_id"=>$user->id];

           return response()->json(['status'=>true,"message"=>"OTP verified successfully.","data"=>$data,"code"=> 200],200);
        }
        else{
          return response()->json(["message"=>"otp not verified , Please try again","status"=>false,"code"=> 400],200);
	        }
	    }
     return response()->json(["message"=>"Either user inactive or does not exists",'status'=>false,"code"=> 400],200);
    
     }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
  }


  

   public function forgetPassword(Request $request){
       	try{
	       $validator = Validator::make($request->all(), [
	            'email_id' => 'required|string|email',
	        ]);

          if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }

        $user=User::where(["email"=>strip_tags(trim($request->email_id)),"status"=>1])->first();
        if(count((array)$user)>0){
	         /* OTP */
	         $digits = 5;
	         $otp = rand(pow(10, $digits - 1) , pow(10, $digits) - 1);
             $user->otp=$otp;

             /*check data saved*/
              if($user->save()){
	             /* View */
	            $view = 'forgot_password';
	            $subject = 'Forgot Password';
	             /* Data array */
	            $data = ['otp' => $otp, 'user_name' => $user->username, 'email' => $user->email];
	            /* Send Mail */
	            $check = Helper::sendEmail($data, $view, $subject);

	            /*send data*/
	            $user_details=["user_id"=>$user->id];


                if($check){
                   return response()->json(["status"=>true,"message"=>"OTP sent to your email to reset your password.","data"=>$user_details,"code"=>200],200); 
                }
                else{
                	 return response()->json(["status"=>false,"message"=>"Please try again later.","code"=>400],200); 
                }
              }
               else{
                	 return response()->json(["status"=>false,"message"=>"Fail to save user data.","code"=>400],200); 
                }
        }

        else{
                	 return response()->json(["status"=>false,"message"=>"User not found!!","code"=>400],200); 
                }
    
     }
         catch (Exception $ex) {
            return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
        }
  }


   public function resetPassword(Request $request){
   	try{

   		 $validator = Validator::make($request->all(), [
	            'user_id' => 'required|string',
	            'password' => 'required|string|min:6',
	        ]);

          if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }
        
        if(strip_tags(trim($request->user_id))!=auth('api')->user()->id){
             return response()->json(["status"=>false,"message"=>"Unauthorized user.","code"=>400],200); 
        }

        $user =User::where(["id"=>strip_tags(trim($request->user_id)),"status"=>1])->first();

        /*if user exists*/
        if(count((array)$user)>0){
           $user->password=bcrypt(strip_tags(trim($request->password)));
           $result=$user->save();

           if($result){
           	 return response()->json(["status"=>true,"message"=>"Reset Password successfully","code"=>200],200); 
           }
           else{
           	 return response()->json(["status"=>false,"message"=>"Please try again later.","code"=>400],200); 
           }
        }
         return response()->json(["status"=>false,"message"=>"Either user inactive or does not exists.","code"=>400],200); 
       
   	}
   	catch(Exception $ex){
        return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
   	}
   }


   public function changePassword(Request $request){
   	try{

   		 $validator = Validator::make($request->all(), [
	            'user_id' => 'required|string',
	            'current_password' => 'required|string',
	            'new_password' => 'required|string|min:6',
	        ]);

          if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            $transformed = [];
            foreach ($errors as $field => $messages) {
                 $transformed[$field] = $messages[0];
            }
            
          return response()->json(
            [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error',
                'result' =>$transformed
                ],
                200
            );
        }
         
         if(strip_tags(trim($request->user_id))!=auth('api')->user()->id){
             return response()->json(["status"=>false,"message"=>"Unauthorized user.","code"=>400],200); 
        }

        $user =User::where("id",strip_tags(trim($request->user_id)))->first();
        $getPwd = User::select('password')->where('id',strip_tags(trim($request->user_id)))->where('user_type_id',Config::get('constants.user_type.user'))->first()->password;

          if (!(Hash::check(strip_tags(trim($request->get('current_password'))), $getPwd))) {
            // The passwords matches
             return response()->json(["status"=>false,"message"=>"Current password not matches","code"=>400],200); 
           }
           else{
           	 $user->password=bcrypt(strip_tags(trim($request->get('new_password'))));
            /*if user updated password successfully*/
           if($user->save()){
           	 return response()->json(["status"=>true,"message"=>"Change Password successfully","code"=>200],200); 
           
               }
            else{
           	 return response()->json(["status"=>false,"message"=>"Fail to change password.","code"=>400],200); 
           }
           }
       
       
   	}
   	catch(Exception $ex){
        return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);
   	}
   }


   /*logout*/
   public function logout(Request $request){
   	 try{

         $user=auth('api')->check();
           
         if ($user){
             auth('api')->user()->token()->revoke();
            // auth('api')->logout();

             return response()->json(['status'=>true,'message'=>"Logout Successfully",'code'=>200],200);

        }
        else 
        {
             return response()->json(['status'=>false,'message'=>"something went wrong",'code'=>500],200);
        }     

   	 }
   	 catch(Exception $ex){
        return response()->json(['message' =>"Please try again later.".$ex->getMessage(),'status'=>false,'code' => 401],200);

   	 }
   }
}
