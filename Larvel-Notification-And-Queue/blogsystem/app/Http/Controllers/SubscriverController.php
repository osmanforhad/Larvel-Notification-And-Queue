<?php

namespace App\Http\Controllers;

use App\Subscriver;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;

class SubscriverController extends Controller
{
    public function store(Request $request){

       //Validation using table name
        $this->validate($request,[
            'email' => 'required|email|unique:subscrivers'
            ]);

        //Call Model
        $subscriber = new Subscriver();
        //user input and db field
        $subscriber->email = $request->email;
        //save data
        $subscriber->save();
        Toastr::success('You Successfully Added to our subscriber list:','Success');
        return redirect()->back();

    }
}
