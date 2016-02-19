<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'role', 'email', 'phone',
    ];
    
    /**
    * Gets the shifts for the current user that have no completed yet
    * 
    * @return \Illuminate\Support\Collection
    **/ 
    public function getUpcomingShifts()
    {
        /**
        * We do some SQL to convert and search by a date since we're using RFC 2822 format in the Database
        * See: https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_convert-tz
        **/
        return UserShift::where('employee_id', $this->id)
            ->whereRaw("CONVERT_TZ(
                str_to_date(`end_time`,'%a, %d %b %Y %T'),
                concat(mid(`end_time`, 27, 3), ':', mid(`end_time`, 30, 2)),
                '+00:00'
                ) >= STR_TO_DATE('" . date('Y-m-d g:i:s') . "', '%Y-%m-%d %h:%i:%s')")
            ->orderBy('start_time', 'ASC')
            ->get();
    }
    
    /**
    * Gets all our completed shifts
    *  - adds an `hours_worked` field
    *
    * @returns \Illuminate\Support\Collection
    **/
    public function getCompletedShifts()
    {
        $final_shift_collection = collect();
        
        $shifts = UserShift::where('employee_id', $this->id)
            ->whereRaw("CONVERT_TZ(
                str_to_date(`end_time`,'%a, %d %b %Y %T'),
                concat(mid(`end_time`, 27, 3), ':', mid(`end_time`, 30, 2)),
                '+00:00'
                ) < STR_TO_DATE('" . date('Y-m-d g:i:s') . "', '%Y-%m-%d %h:%i:%s')")
            ->orderBy('start_time', 'ASC')
            ->get();
        
        # For each shift, figure out the hours worked
        foreach($shifts as $shift) {
            $start_time = new \DateTime($shift->start_time);
            $end_time   = new \DateTime($shift->end_time);
            
            # Get the difference between these times
            $interval= $start_time->diff($end_time);
            
            # Get our hours worked
            $shift->hours_worked = (($interval->days * 24) + $interval->h) - $shift->break;
            # Get our minutes worked, represented as part of an hour
            $shift->hours_worked += ( .01 * ($interval->i * 100 / 60));
            # Format
            $shift->hours_worked = number_format($shift->hours_worked, 3);
            
            # Add it to our return collection
            $final_shift_collection->push($shift);
        }
        
        # Return our collection
        return $final_shift_collection;
    }
    
    /**
    * Gets our completed shifts by week and only return that information
    * 
    * @returns array $return_weeks
    **/
    public function getCompletedShiftsByWeek()
    {
        $years = [];
        $return_weeks = [];
        
        foreach($this->getCompletedShifts() as $shift) {
            $end_time = new \DateTime($shift->end_time);
            
            $years[$end_time->format('Y')][$end_time->format('W')] =+ $shift->hours_worked;
        }
        
        foreach($years as $year => $year_value) {
            foreach($year_value as $week => $hours) {    
                $return_weeks[] = (object) [
                    'year' => $year,
                    'week' => $week,
                    'hours' => $hours,
                ];
            }
        }
        
        return $return_weeks;
    }
    
    public function isEmployee()
    {
        return $this->role == 'employee' ? true : false;
    }
    
    public function isManager()
    {
        return $this->role == 'manager' ? true : false;
    }
    
}
