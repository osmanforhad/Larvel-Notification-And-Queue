<?php

namespace App\Http\Controllers\Admin;

use App\Subscriver;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SubscriverController extends Controller
{
    public function index(){

        $subscribers = Subscriver::latest()->get();
        return view('admin.subscriber',compact('subscribers'));
    }

    public function destroy($subscriber){
        //Use Fail for display user friendly error msg
        $subscriber = Subscriver::findorFail($subscriber);
        $subscriber->delete();
        Toastr::success('Subscriber Successfully Delete','success');
        return redirect()->back();

    }
}
