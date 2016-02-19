<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use App\User;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public $AuthUser = null;
    
    public function __construct(Request $request)
    {
        # Make sure we have a token
        if( ! $request->has('token')) {
            return abort(401);
        }
        
        # Look for our user
        $user = User::where('token', $request->get('token'))->first();
        
        # Make sure we found someone
        if( ! $user) {
            return abort(404);
        }
        
        # Set the user for all controllers
        $this->AuthUser = $user;
    }
    
}
