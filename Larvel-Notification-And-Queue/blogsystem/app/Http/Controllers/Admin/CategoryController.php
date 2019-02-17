<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //For display data from categories table
        $categories = Category::latest()->get();
        return view('admin.category.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.category.create');
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
            'name' => 'required|unique:categories',
            'image' => 'required|mimes:jpeg,bmp,png,jpg'
        ]);
        //Get image through user input
        $image = $request->file('image');
        $slug = str_slug($request->name);
        if (isset($image)){
            //make unique name for image
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'_'.$currentDate.'_'.uniqid().'.'.$image->getClientOriginalExtension();
            //check folder name for category image upload
            if (!Storage::disk('public')->exists('category')){
                //for create folder
                Storage::disk('public')->makeDirectory('category');
            }
            //Resize image for category using intervention package
            $resizecatImg = Image::make($image)->resize(1600,479)->save($image->getClientOriginalExtension());
            //Move Image into Specific folder which created
            Storage::disk('public')->put('category/'.$imagename,$resizecatImg);

            //check folder name for category slider image upload
            if (!Storage::disk('public')->exists('category/slider')){
                //for create folder
                Storage::disk('public')->makeDirectory('category/slider');
            }
            //Resize image for category slider using intervention package
            $resizesliderImg = Image::make($image)->resize(500,333)->save($image->getClientOriginalExtension());
            Storage::disk('public')->put('category/slider/'.$imagename,$resizesliderImg);
        } else{
            $imagename = 'default.png';
        }
        //Initial model
        $category = new Category();
//Database field and user input
        $category->name = $request->name;
        $category->slug = $slug;
        $category->image = $imagename;
        //For insert data
        $category->save();
        //For Displaying Message using Toaster Package
        Toastr::success('Category Successfully Saved :)','Success');
        return redirect()->route('admin.category.index');
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
        //get gata from db through model
        $category = Category::find($id);
        return view('admin.category.edit',compact('category'));
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
        //Validation
        $this->validate($request,[
            'name' => 'required',
            'image' => 'mimes:jpeg,bmp,png,jpg'
        ]);
        //Get image through user input
        $image = $request->file('image');
        $slug = str_slug($request->name);
        //hold old info using model
        $category = Category::find($id);
        if (isset($image)){
            //make unique name for image
            $currentDate = Carbon::now()->toDateString();
            $imagename = $slug.'_'.$currentDate.'_'.uniqid().'.'.$image->getClientOriginalExtension();
            //check folder name for category image upload
            if (!Storage::disk('public')->exists('category')){
                //for create folder
                Storage::disk('public')->makeDirectory('category');
            }
            //delete old image for category
            if (Storage::disk('public')->exists('category/'.$category->image)){
                Storage::disk('public')->delete('category/'.$category->image);
            }
            //Resize image for category using intervention package
            $resizecatImg = Image::make($image)->resize(1600,479)->save($image->getClientOriginalExtension());
            Storage::disk('public')->put('category/'.$imagename,$resizecatImg);

            //check folder name for category slider image upload
            if (!Storage::disk('public')->exists('category/slider')){
                //for create folder
                Storage::disk('public')->makeDirectory('category/slider');
            }
            //delete old image for category slider
            if (Storage::disk('public')->exists('category/slider/'.$category->image)){
                Storage::disk('public')->delete('category/slider/'.$category->image);
            }
            //Resize image for category slider using intervention package
            $resizesliderImg = Image::make($image)->resize(500,333)->save($image->getClientOriginalExtension());
            Storage::disk('public')->put('category/slider/'.$imagename,$resizesliderImg);
        } else{
            $imagename = $category->image;
        }

//Database field and user input
        $category->name = $request->name;
        $category->slug = $slug;
        $category->image = $imagename;
        //For insert data
        $category->save();
        //For Displaying Message using Toaster Package
        Toastr::success('Category Successfully Updated :)','Success');
        return redirect()->route('admin.category.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //get gata from db through model
        $category = Category::find($id);
        //Check catimage for delete
        if (Storage::disk('public')->exists('category/'.$category->image)){
            //For delete Catimage from folder
            Storage::disk('public')->delete('category/'.$category->image);
        }

        //Check category-slider image for delete
        if (Storage::disk('public')->exists('category/slider/'.$category->image)){
            //For delete category-slider image from folder
            Storage::disk('public')->delete('category/slider/'.$category->image);
        }
        //delete data from db
        $category->delete();
        //For Displaying Message using Toaster Package
        Toastr::success('Category Successfully Delete :)','Success');
        return redirect()->back();
    }
}
