<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;

class ImagesController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        return view("imageupload");
    }

    public function upload(Request $request)
    {

        if ($request->hasFile('image')) {

            // dd($request->image);
            $image    = $request->image;
            $filename = time() . '.' . $image->getClientOriginalExtension();

            $path = public_path('images/' . $filename);
            echo $path;
            Image::make($image->getRealPath())->resize(200, 200)->save($path);
            // $user->image = $filename;
            // $user->save();
        }
        return;
    }

}
