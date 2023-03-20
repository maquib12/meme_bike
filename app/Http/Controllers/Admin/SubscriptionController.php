<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\TableUserType;
use App\Models\Advertisement;
use App\Models\AdvertisementLocation;
use App\Models\UserSubscription;
use App\Models\Subscription;
use App\Models\RideRoutes;
use App\Models\UserRide;
use App\Models\EnvironmentType;
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

class SubscriptionController extends Controller
{
    public function subscriptionManagement(){
        Carbon::setWeekStartsAt(Carbon::SUNDAY);
        Carbon::setWeekEndsAt(Carbon::SATURDAY);

        $totalUser = User::where('user_type_id',2)->count();
        $totalUserSubscription= User::rightjoin('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
                     ->where(['users.user_type_id'=>Config::get('constants.user_type.user')])
                     ->count();

        $totalSubscription= User::rightjoin('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
                     ->where(['users.user_type_id'=>Config::get('constants.user_type.user')])
                     ->where('user_subscriptions.status',1)
                     ->count();
        // $userSubscriberPercentage = number_format(($totalUserSubscription/$totalUser) * 100,0);
        // $totalSubcriberPer = number_format(($totalSubscription/$totalUser) * 100,0);
        $newSubscription = UserSubscription::where('created_at','>=',Carbon::now()->subdays(15))
                                              ->where('status',1)->count();

        // $newSubscriptionPer = number_format(($newSubscription/$totalSubscription) * 100,0);

        // Month wise Chart
        $subscriptionMonth = array();
        for($i = 1; $i<=12; $i++){
            $a = 12 -$i;

           $total = User::rightjoin('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
                                ->where(['users.user_type_id'=>Config::get('constants.user_type.user')])
                                 ->where('user_subscriptions.status',1)
                               ->whereYear('user_subscriptions.created_at', date('Y'))
                               ->whereMonth('user_subscriptions.created_at', '=', $i)
                               ->count();
                               array_push($subscriptionMonth,$total);
        }
        //dd($subscriptionMonth);

        //Week Wise Chart
        $subscriptionWeek = array();
        
        for($i = 0; $i < 7; $i++){
            $y = Carbon::now()->subDay($i)->format('Y');
            $m = Carbon::now()->subDay($i)->format('m');
            $d = Carbon::now()->subDay($i)->format('d');
            $totalWeekUser = User::rightjoin('user_subscriptions', 'user_subscriptions.user_id', '=', 'users.id')
                                   ->where(['users.user_type_id'=>Config::get('constants.user_type.user')])
                                    ->where('user_subscriptions.status',1)
                                
                                    ->whereYear('user_subscriptions.created_at','=', $y)
                                    ->whereMonth('user_subscriptions.created_at','=', $m)
                                    ->whereDay('user_subscriptions.created_at','=', $d)
                                      ->count();

                
                            array_push($subscriptionWeek,$totalWeekUser);
        }
        $subscriptionWeek = array_reverse($subscriptionWeek);

        //Year Wise Chart
        $subscriptionYear = array();
        for($i = 0; $i< 10; $i++){
           $now = Carbon::now()->subYear($i)->format('Y');
            //dd($now);
           $totalUserYear = User::select(DB::raw("COUNT(*) as count"))
                          ->whereYear('created_at','=', $now)
                          ->where('user_type_id',2)
                           ->where('status',1)
                           ->pluck('count');
            array_push($subscriptionYear,$totalUserYear);
        }
        $subscriptionYear = array_reverse($subscriptionYear);

        $totalPlan = Subscription::leftjoin('user_subscriptions','user_subscriptions.subscription_id', '=' , 'subscriptions.id')
        ->where('user_subscriptions.status',1)
        ->first();

        return view('admin.subscription-management',compact('newSubscription',
        'totalSubscription','totalUserSubscription','subscriptionMonth','subscriptionWeek','subscriptionYear','totalPlan'));
    }
}
