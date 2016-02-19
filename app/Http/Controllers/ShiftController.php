<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\UserShift;
use App\User;

class ShiftController extends Controller
{
    /**
     * Gets the users that are working during a specified shift
     * 
     * @param  int  $shift_id 
     * @return json           JSON array of user objects
     */
    public function getCoworkers($shift_id)
    {
        $shift = UserShift::find($shift_id);
        
        # Make sure we have access
        if($this->AuthUser->isEmployee() AND $this->AuthUser->id !== $shift->employee_id) {
            return abort(401);
        }
        
        return response()->json($shift->getCoworkers());
    }
    
    /**
     * Gets shift history for a given user
     * 
     * @param  int  $employee_id 
     * @return json              JSON Array of objects of weekly hours worked
     */
    public function getHistory($employee_id)
    {
        $user = User::find($employee_id);
        
        # Make sure we have access
        if($this->AuthUser->isEmployee() AND $this->AuthUser->id !== $user->id) {
            return abort(401);
        }
        
        return response()->json($user->getCompletedShiftsByWeek());
    }
    
    /**
     * Gets the manager for a shift
     * 
     * @param  int  $shift_id 
     * @return json           JSON user object
     */
    public function getManager($shift_id)
    {
        $shift = UserShift::find($shift_id);
        
        # Make sure we have access
        if($this->AuthUser->isEmployee() AND $this->AuthUser->id !== $shift->employee_id) {
            return abort(401);
        }
        
        # Get the manager for this shift
        $user = User::find($shift->manager_id);
        
        if( ! $user) {
            return abort(404);
        }
        
        return response()->json($user);
    }
    
    public function getByDate()
    {
        # Make sure we have access
        if( ! $this->AuthUser->isManager()) {
            return abort(401);
        }
        
        $shift_model = new UserShift();
        $start_time = new \DateTime(request()->get('start_time'));
        $end_time   = new \DateTime(request()->get('end_time'));
        
        return response()->json( $shift_model->getByDateRange($start_time, $end_time) );
    }
    
    /**
     * Creates a shift
     * 
     * @return json 
     */
    public function postCreate()
    {
        # Make sure we have access
        if( ! $this->AuthUser->isManager()) {
            return abort(401);
        }
        
        $start_time = new \DateTime(request()->get('start_time'));
        $end_time   = new \DateTime(request()->get('end_time'));
        
        $shift = new UserShift();
        
        $shift->manager_id  = $this->AuthUser->id;
        $shift->employee_id = request()->get('employee_id');
        $shift->break       = request()->get('break');
        $shift->start_time  = $start_time->format(\DateTime::RFC2822);
        $shift->end_time    = $end_time->format(\DateTime::RFC2822);
        
        $shift->save();
        
        return response()->json($shift);
    }
    
    /**
     * Alternate method for creating a shift
     */
    public function putCreate()
    {
        $this->postCreate();
    }
    
    /**
     * Updates a shift
     */
    public function postUpdate()
    {
        # Make sure we have access
        if( ! $this->AuthUser->isManager()) {
            return abort(401);
        }
        
        # First make sure we have an ID, if not, create a shift instead of updating
        if(request()->has('user_shift_id')) {
            return abort(404);
        } elseif( ! $shift = UserShift::find(request()->get('user_shift_id'))) {
            return $this->postCreate();
        }
        
        $start_time = new \DateTime(request()->get('start_time'));
        $end_time   = new \DateTime(request()->get('end_time'));
        
        $shift->manager_id  = $this->AuthUser->id;
        $shift->employee_id = request()->get('employee_id');
        $shift->break       = request()->get('break');
        $shift->start_time  = $start_time->format(\DateTime::RFC2822);
        $shift->end_time    = $end_time->format(\DateTime::RFC2822);
        
        $shift->save();
        
        return response()->json($shift);
    }
    
    /**
     * Alternate method for updating a shift
     */
    public function putUpdate()
    {
        $this->postUpdate();
    }
    
}
