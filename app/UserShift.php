<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserShift extends Model
{
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'manager_id', 'employee_id', 'break', 'start_time', 'end_time',
    ];
    
    /**
    * Gets all the users that are working during a shift, except the one that belongs to this shift
    * - @TODO: Can use a more efficient SQL JOIN
    * 
    * @return \Illuminate\Support\Collection
    **/ 
    public function getCoworkers()
    {
        $users = [];
        
        # First get all our corresponding shifts
        $other_shifts = UserShift::whereRaw("CONVERT_TZ(
                STR_TO_DATE(`start_time`,'%a, %d %b %Y %T'),
                concat(mid(`start_time`, 27, 3), ':', mid(`start_time`, 30, 2)),
                '+00:00'
                ) >= STR_TO_DATE('" . date('Y-m-d g:i:s', strtotime($this->start_time)) . "', '%Y-%m-%d %h:%i:%s'")
            ->whereRaw("CONVERT_TZ(
                STR_TO_DATE(`end_time`,'%a, %d %b %Y %T'),
                concat(mid(`end_time`, 27, 3), ':', mid(`end_time`, 30, 2)),
                '+00:00'
                ) <= STR_TO_DATE('" . date('Y-m-d g:i:s', strtotime($this->end_time)) . "', '%Y-%m-%d %h:%i:%s'")
            ->where('employee_id', '!=', $this->employee_id)
            ->get();
        
        # Put all these users IDs into an array
        foreach($other_shifts as $shift) {
            $users[] = $shift->employee_id;
        }
        
        # Now get our user objects
        return User::whereIn('id', $users)->get();    
    }
    
    /**
    * Gets all shifts for a specific time period
    * 
    * @return \Illuminate\Support\Collection
    **/ 
    public function getByDateRange(\DateTime $start_time, \DateTime $end_time)
    {
        /**
        * We do some SQL to convert and search by a date since we're using RFC 2822 format in the Database
        * See: https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_convert-tz
        **/
        return UserShift::whereRaw("CONVERT_TZ(
                str_to_date(`start_time`,'%a, %d %b %Y %T'),
                concat(mid(`start_time`, 27, 3), ':', mid(`start_time`, 30, 2)),
                '+00:00'
                ) >= STR_TO_DATE('" . $start_time->format('Y-m-d g:i:s') . "', '%Y-%m-%d %h:%i:%s')")
            ->whereRaw("CONVERT_TZ(
                str_to_date(`start_time`,'%a, %d %b %Y %T'),
                concat(mid(`start_time`, 27, 3), ':', mid(`start_time`, 30, 2)),
                '+00:00'
                ) <= STR_TO_DATE('" . $end_time->format('Y-m-d g:i:s') . "', '%Y-%m-%d %h:%i:%s')")
            ->orderBy('start_time', 'ASC')
            ->get();
    }
    
}
