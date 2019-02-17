<?php

namespace App\Http\Controllers\Author;

use App\Category;
use App\Notifications\NewAuthorPost;
use App\Tag;
use App\Post;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /**retrive data for Authenticated user Through posts method
        which relation defile in User model**/
        $posts = Auth::user()->posts()->latest()->get();
        return view('author.post.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        /**Initial category and Tag Model for
        sent data from cat and tag table in view using variable*/
        $categories = Category::all();
        $tags = Tag::all();
        return view('author.post.create',compact('categories','tags'));
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
            'title' => 'required',
            'image' => 'required|mimes:jpeg,bmp,png,jpg',
            'categories' => 'required',
            'tags' => 'required',
            'body' => 'required',
        ]);
        //get Image through user input
        $image = $request->file('image');
        $slug = str_slug($request->title);
        if (isset($image)){
            //Make Unique name for image
            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            //Check post Folder for upload Feature photo
            if (!Storage::disk('public')->exists('post')){
                //for create folder
                Storage::disk('public')->makeDirectory('post');
            }
            //For Resize image
            $resizepostImg = Image::make($image)->resize(1600,1066)->save($image->getClientOriginalExtension());
            //Move Image into Specific folder which Created
            Storage::disk('public')->put('post/'.$imageName,$resizepostImg);

        } else{
            $imageName = "default.png";
        }
        //Initial Model
        $post = new Post();
        //For save login user who create this post
        $post->user_id = Auth::id();
        //Database field and user input
        $post->title = $request->title;
        $post->slug = $slug; //as we get this using this variable
        $post->image = $imageName; //as we get this using this variable
        $post->body = $request->body;
        if(isset($request->status)){ //check for  selected input
            $post->status = true;
        } else{
            $post->status = false;
        }
        $post->is_approved = false; //Author can't approved post thats why define false
        //For insert data
        $post->save();
        //For insert/attach Relational Table data
        $post->categories()->attach($request->categories); //call categories method which is define in Post Model
        $post->tags()->attach($request->tags);//call tags method which is define in Post Model

        //Send notification to Admin
        $users = User::where('role_id','1')->get();
        Notification::send($users, new NewAuthorPost($post));

        //For Displaying Message using Toaster Package
        Toastr::success('Post Successfully Saved :)','Success');
        return redirect()->route('author.post.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        /*Post Authentication check*/
        if ($post->user_id != Auth::id()){
            Toastr::error('You are not authorize to access this post','Error');
            return redirect()->back();
        }
        return view('author.post.show',compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        /*Post Authentication check*/
        if ($post->user_id != Auth::id()){
            Toastr::error('You are not authorize to access this post','Error');
            return redirect()->back();
        }
        /**Initial category and Tag Model for
        sent data from cat and tag table in view using variable*/
        $categories = Category::all();
        $tags = Tag::all();
        return view('author.post.edit',compact('post','categories','tags'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        /*Post Authentication check*/
        if ($post->user_id != Auth::id()){
            Toastr::error('You are not authorize to access this post','Error');
            return redirect()->back();
        }
        //Validation
        $this->validate($request,[
            'title' => 'required',
            'image' => 'image',
            'categories' => 'required',
            'tags' => 'required',
            'body' => 'required',
        ]);
        //get Image through user input
        $image = $request->file('image');
        $slug = str_slug($request->title);
        if (isset($image)){
            //Make Unique name for image
            $currentDate = Carbon::now()->toDateString();
            $imageName = $slug.'-'.$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
            //Check post Folder for upload Feature photo
            if (!Storage::disk('public')->exists('post')){
                //for create folder
                Storage::disk('public')->makeDirectory('post');
            }
            //Delete old image from post
            if (Storage::disk('public')->exists('post/'.$post->image)){
                Storage::disk('public')->delete('post/'.$post->image);
            }
            //For Resize image
            $resizepostImg = Image::make($image)->resize(1600,1066)->save($image->getClientOriginalExtension());
            //Move Image into Specific folder which Created
            Storage::disk('public')->put('post/'.$imageName,$resizepostImg);

        } else{
            //Save existing image
            $imageName = $post->image;
        }
        //For save login user who create this post
        $post->user_id = Auth::id();
        //Database field and user input
        $post->title = $request->title;
        $post->slug = $slug; //as we get this using this variable
        $post->image = $imageName; //as we get this using this variable
        $post->body = $request->body;
        if(isset($request->status)){ //check for  selected input
            $post->status = true;
        } else{
            $post->status = false;
        }
        $post->is_approved = false; //Author can't approved post thats why define false
        //For insert data
        $post->save();
        //For insert/attach Relational Table data
        $post->categories()->sync($request->categories); //call categories method which is define in Post Model
        $post->tags()->sync($request->tags);//call tags method which is define in Post Model

        //For Displaying Message using Toaster Package
        Toastr::success('Post Successfully Updated :)','Success');
        return redirect()->route('author.post.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        /*Post Authentication check*/
        if ($post->user_id != Auth::id()){
            Toastr::error('You are not authorize to access this post','Error');
            return redirect()->back();
        }
        //Check PostImage for delete
        if (Storage::disk('public')->exists('post/'.$post->image)){
            //For delete Image from Folder
            Storage::disk('public')->delete('post/'.$post->image);
            /**For Delete Post Related Category
            by calling categories method from Post Model**/
            $post->categories()->detach();
            /**For Delete Post Related Tag
            by calling tags method from Post Model**/
            $post->tags()->detach();

            //delete data from db
            $post->delete();
            //For Displaying Message using Toaster Package
            Toastr::success('Post Successfully Delete :)','Success');
            return redirect()->back();
        }
    }
}
