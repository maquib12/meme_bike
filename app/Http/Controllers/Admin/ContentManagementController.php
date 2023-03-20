<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
use App\Models\StaticContent;
use Validator;
use Auth;
use Session;
use Cache;
use Toastr;
use Helper;
use Image;
use Hash;

class ContentManagementController extends Controller
{
    public function aboutUs(){
        $aboutUs = StaticContent::where('content_type',1)->where('status',1)->first();
        return view('admin.about-us',compact('aboutUs'));
    }

    public function privacyPolicy(){
        $privacyPolicy = StaticContent::where('content_type',3)->where('status',1)->first();
        return view('admin.privacy-policy',compact('privacyPolicy'));
    }

    public function termsCondition(){
        $termsCondition = StaticContent::where('content_type',2)->where('status',1)->first();
        return view('admin.terms-n-condition',compact('termsCondition'));
    }


    public function editAboutUs(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data=$request->all();
            $about=StaticContent::where('content_type',1)->update(['description'=>$data['edit_about_us']]);
            return redirect()->route('about-us');
            Toastr::success('Content Updated Successfully ','Success');
        }
        $about_us= StaticContent::where('content_type',1)->first();
        return view('admin.about-us-edit',compact('about_us'));
    }

    public function editPrivacy(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data=$request->all();
            $privacy=StaticContent::where('content_type',3)->update(['description'=>$data['edit_privacy']]);
            return redirect()->route('privacy-policy');
            Toastr::success('Content Updated Successfully ','Success');
        }
        $privacy= StaticContent::where('content_type',3)->first();
        return view('admin.privacy-policy-edit',compact('privacy'));
    }

    public function editTerms(Request $request)
    {
        if($request->isMethod('post'))
        {
            $data=$request->all();
            $terms=StaticContent::where('content_type',2)->update(['description'=>$data['edit_terms']]);
            return redirect()->route('terms-n-condition');
            Toastr::success('Content Updated Successfully ','Success');
        }
        $terms= StaticContent::where('content_type',2)->first();
        return view('admin.terms-n-condition-edit',compact('terms'));
    }
}
