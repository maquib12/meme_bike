<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
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
use Response;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(){
        return view('admin.forgot_password');
    }

    public function otp(Request $request)
    {

        $email = $request->identity;
        return view('admin.otp', compact('email'));
    }



    public function forgotPasswordPost(Request $request)
    {

        $validator = Validator::make($request->all() , ['email' => 'required']);
        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
            }
            return redirect()->back()
                ->withInput();
        }
        else
        {
            try
            {
                $email = $request->email;
                $user = User::where('email', $email)->where('user_type_id',1)
                    ->where('status', 1)
                    ->first();
                if ($user)
                {

                    /* OTP */
                    $digits = 5;
                    $otp = rand(pow(10, $digits - 1) , pow(10, $digits) - 1);

                    /* Data array */
                    $data = ['otp' => $otp, 'user_name' => $user->first_name, 'email' => $user->email];
                    /* View */
                    $view = 'forgot_password';
                    $subject = 'Forgot Password';

                    /* Send Mail */
                    $check = Helper::sendEmail($data, $view, $subject);
                    $user->otp = $otp;
                    $user->is_otp_verified = 0;
                    $user->save();

                    Toastr::success("Otp sent to your registered email.", $title = "Success", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    //return redirect('otp?identity='.$email)->with('message', 'Otp sent to your registered email!');
                    return redirect('otp?identity=' . $email);
                }
                else
                {
                    Toastr::error("Please enter valid email address.", $title = "Failed", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    //return redirect()->back()->with('error', 'Please enter valid email address.');
                    return redirect()->back();
                }
            }
            catch(Exception $e)
            {
                Toastr::error($e->getMessage() , 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
                return redirect()
                    ->back();
            }
        }
    }



    public function resendOtp(Request $request)
    {

        $validator = Validator::make($request->all() , ['email' => 'required']);
        if ($validator->fails())
        {
            return Response::json(['status' => 0, 'message' => 'Please enter email.']);
        }
       
        $user = User::where('email', $request->email)
                ->where('user_type_id',1)
                ->where('status', 1)
                ->first();
        if ($user)
        {

            /* OTP */
            $digits = 5;
            $otp = rand(pow(10, $digits - 1) , pow(10, $digits) - 1);

            /* Data array */
            $data = ['otp' => $otp, 'user_name' => $user->first_name, 'email' => $user->email];

            /* View */
            $view = 'forgot_password';
            $subject = 'Forgot Password';

            /* Send Mail */
            $check = Helper::sendEmail($data, $view, $subject);

            $user->otp = $otp;
            $user->is_otp_verified = 0;
            $user->save();

            return Response::json(['status' => 200, 'email' => $user->email, 'message' => 'Otp sent to your registered email.']);
        }
        else
        {
            return Response::json(['status' => 0]);
        }
    }


    public function otpVerify(Request $request)
    {

        $validator = Validator::make($request->all() , ['otp' => 'required']);
        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
            }
            return redirect()->back()
                ->withInput();
        }
        else
        {
            try
            {
                $string = implode("", $request->otp);
                $email = $request->email;

                $verify = User::where('otp', $string)->where('email', $email)->where('user_type_id',1)
                    ->where('status', 1)
                    ->first();
                if ($verify)
                {

                    $verify->is_otp_verified = 1;
                    $verify->update();

                    $email = $request->email;
                    Toastr::success("Otp verified.", $title = "Success", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    return redirect('reset-password?identity=' . $email);
                }
                else
                {
                    Toastr::error("Please enter valid OTP.", $title = "Failed", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    return redirect('otp?identity=' . $email);
                }
            }
            catch(Exception $e)
            {
                Toastr::error($e->getMessage() , 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
                return redirect()
                    ->back();
            }
        }
    }


    public function resetPassword(Request $request)
    {

        $email = $request->identity;

        return view('admin.reset-password', compact('email'));
    }



    public function resetPasswordPost(Request $request)
    {

        $validator = Validator::make($request->all() , ['email' => 'required', 'password' => 'required', 'confirm_password' => 'required_with:password|same:password']);

        if ($validator->fails())
        {
            $messages = $validator->messages();
            foreach ($messages->all() as $message)
            {
                Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
            }
            return redirect()->back()
                ->withInput();
        }
        else
        {
            try
            {

                $user = User::where('email', $request->email)
                    ->where('user_type_id', 1)
                    ->where('status', 1)
                    ->first();

                $user->password = Hash::make($request->password);
                $user->save();

                Toastr::success('Password changed Successfully!', 'Success', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
                return redirect()
                    ->route('login');
            }
            catch(Exception $e)
            {
                Toastr::error($e->getMessage() , 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
                return redirect()
                    ->back();
            }
        }
    }


    public function changePassword(Request $request)
        {
            if($request->isMethod('post'))
            {
                if (!(Hash::check($request->get('current_password') , Auth::user()
                ->password)))
                {
                    // The passwords matches
                    Toastr::error("Your current password does not match with the password you provided.", $title = "Authentication Failed", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    return redirect()->back();
                }
                if (strcmp($request->get('current_password') , $request->get('new_password')) == 0)
                {
                    //Current password and new password are same
                    Toastr::error("New Password cannot be same as your current password. Please choose a different password.", $title = "Failed", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    Toastr::clear();
                    return redirect()->back();
                }
                $validator = Validator::make($request->all() , ['current_password' => 'required', 'new_password' => 'required|min:8|max:16', 'c_password' => 'required|min:8|max:16|required_with:new_password|same:new_password']);
                 
                if ($validator->fails())
                {
                    $messages = $validator->messages();
                    foreach ($messages->all() as $message)
                    {
                        Toastr::error($message, 'Failed', ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                    }
                    return redirect()->back()
                        ->withErrors($validator)->withInput();
                }
                $user = Auth::user();
                $user->password = bcrypt($request->get('new_password'));
                $user->save();
                Toastr::success("Password Changed Successfully.", $title = "Success", ['timeOut' => 10000], ["positionClass" => "toast-top-center"]);
                Toastr::clear();
                return redirect()->route('dashboard');
            }
            $admin= User::where(['user_type_id'=>Config::get('constants.user_type.Admin')])->first();
            return view('admin.change_password',compact('admin'));
    }


}



