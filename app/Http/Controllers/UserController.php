<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class UserController extends Controller
{    
    /**
     * Gets the shifts for an employee
     * 
     * @param  int  $employee_id User ID of employee
     * @return json              JSON array of shifts
     */
    public function getShifts($employee_id = null)
    {
        # Make sure we have access
        if($this->AuthUser->isEmployee() AND $this->AuthUser->id !== $employee_id) {
            return abort(401);
        }
        
        $user = User::find($employee_id);
        
        # Make sure we found a user
        if( ! $user) {
            return abort(404);
        }
        
        return response()->json($user->getUpcomingShifts());
    }
    
    /**
     * Get details of an employee
     * 
     * @param  int  $employee_id 
     * @return json              JSON object of user
     */
    public function getView($employee_id)
    {
        # Make sure we have access
        if( ! $this->AuthUser->isManager() OR ($this->AuthUser->isEmployee() AND $this->AuthUser->id != $employee_id) ) {
            return abort(401);
        }
        
        $user = User::find($employee_id);
        
        # Make sure we found a user
        if( ! $user) {
            return abort(404);
        }
        
        return response()->json($user);
    }
    
}
