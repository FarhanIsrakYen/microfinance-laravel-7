<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use DB;

class CheckMfnSetting
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // If branch Date is not assinged
        $branchSoftStartingDate = DB::table('gnl_branchs')->where('id', Auth::user()->branch_id)->select('mfn_start_date')->first()->mfn_start_date;

        if (is_null($branchSoftStartingDate)) {

            $notification = array(
                'message'    => 'Your MFN software start date is empty!!',
                'alert-type' => 'warning',
            );
            
            return redirect('/')->with($notification);
        }

        // If MFN Configuration is not completed
        $isConfigCompleted = DB::table('mfn_config')->where('title', 'isConfigCompleted')->first()->content;

        if ($isConfigCompleted != 'yes') {
            return redirect('mfn/initialSetting');
        }

        return $next($request);
    }
}
