<?php

namespace App\Http\Controllers\Admin;

use App\Tag;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //For display data from tags table
        $tags = Tag::latest()->get();
        return view('admin.tag.index',compact('tags'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.tag.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //Validation
        $this->validate($request,[
            'name' => 'required|unique:tags'
        ]);
        //Model Name
        $tag = new Tag();
        //Database field and user input
        $tag->name = $request->name;
        $tag->slag = str_slug($request->name);
        //For insert Data
        $tag->save();
//For Displaying Message using Toaster Package
        Toastr::success('Tag Successfully Saved :)','Success');

        return redirect()->route('admin.tag.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //Find id using model
        $tag = Tag::find($id);

        return view('admin.tag.edit',compact('tag'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Find id using model
        $tag = Tag::find($id);
        //Database field and user input
        $tag->name = $request->name;
        $tag->slag = str_slug($request->name);
        //For Update Data
        $tag->save();
        //For Displaying Message
        Toastr::success('Tag Successfully Updated :)','Success');
        return redirect()->route('admin.tag.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //Call the model and delete data
        Tag::find($id)->delete();
        //For Displaying Message
        Toastr::success('Tag Successfully Deleted :)','Success');
        return redirect()->back();
    }
}
