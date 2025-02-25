<?php

namespace App\Http\Controllers\Rapoarte;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BusinessUnitGroup;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;

class RaportSituatiePrezentaLunaraController extends Controller
{
    public function index()
    {
        // Get all business unit groups ordered by sel_order
        $businessUnitGroups = BusinessUnitGroup::orderBy('sel_order')
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name
                ];
            });
    
        // Get current month's data
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get all schedules for current month
        $schedules = EmployeeSchedule::with(['employee', 'scheduleStatus'])
            ->whereYear('date_start', $currentYear)
            ->whereMonth('date_start', $currentMonth)
            ->orderBy('employee_id')
            ->get();
    
        // Group schedules by employee
        $employeeSchedules = $schedules->groupBy('employee_id');
        
        // Process data for the view
        $people = [];
        foreach($employeeSchedules as $employeeId => $employeeData) {
            $employee = $employeeData->first()->employee;
            $monthData = [
                'name' => $employee->full_name,
                'hours' => [],
                'totalHours' => 0,
                'spor' => 0, // Special conditions hours
                'details' => []
            ];
    
            // Initialize all days
            for($i = 1; $i <= 31; $i++) {
                $monthData['hours'][$i] = 0;
            }
    
            // Fill in schedule data
            foreach($employeeData as $schedule) {
                $day = (int)$schedule->date_start->format('d');
                
                if($schedule->schedule_status_id === 1) { // Regular work
                    $monthData['hours'][$day] = $schedule->total_hours;
                    $monthData['totalHours'] += $schedule->total_hours;
                } else {
                    // Handle special statuses (CO, CM, etc)
                    $monthData['hours'][$day] = $schedule->scheduleStatus->code;
                    if($schedule->scheduleStatus->code === 'CO') {
                        $monthData['details'][] = "CO: {$schedule->total_hours} ore";
                    }
                }
            }
    
            $people[] = $monthData;
        }
    
        // Return view with data
        return Inertia::render('Rapoarte/RaportSituatiePrezentaLunara')
            ->with([
                "businessUnitGroups" => $businessUnitGroups,
                "people" => $people,
            ]);
    }

    public function getMonthlyData(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'business_unit_group_id' => 'required|integer' // Add this validation
            ]);
    
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
    
            // Get all schedules for the month
            $query = EmployeeSchedule::with(['employee.businessUnitEmployees', 'scheduleStatus'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('date_start', [$startDate, $endDate])
                  ->orWhereBetween('date_finish', [$startDate, $endDate]);
            })
            ->whereHas('employee.businessUnitEmployees.businessUnit', function($q) use ($request) {
                $q->where('business_unit_group_id', $request->business_unit_group_id);
            })
            ->whereNull('deleted_at');

            $schedules = $query->get();
    
            // Process the data
            $processedData = $this->processMonthlyData($schedules, Carbon::parse($request->start_date));

            return response()->json([
                'people' => $processedData,
                'month_details' => [
                    'working_days' => $this->calculateWorkingDays(Carbon::parse($request->start_date)),
                    'total_hours' => $this->calculateWorkingDays(Carbon::parse($request->start_date)) * 8
                ]
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'A apărut o eroare la procesarea datelor.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    private function processMonthlyData($schedules, Carbon $date)
    {
        $employeeSchedules = $schedules->groupBy('employee_id');
        $processedData = [];
        $sporStatusCodes = ['PREZ', 'R*', 'LS', 'M'];
        $maxDailySpor = 8;
    
        foreach ($employeeSchedules as $employeeId => $employeeData) {
            $employee = $employeeData->first()->employee;
            $monthData = [
                'name' => $employee->full_name,
                'compartment_id' => $employee->compartment_id,
                'hours' => array_fill(1, $date->daysInMonth, 0),
                'totalHours' => 0,
                'spor' => 0,
                'details' => []
            ];
    
            // Status tracking arrays
            $statusSummary = [];
            $daySpecificStatuses = [];
            $daySpecificCodes = ['R', 'LS', 'R*'];
    
            $schedulesByDay = $employeeData->groupBy(function($schedule) {
                return $schedule->date_start->format('d');
            });
    
            foreach ($schedulesByDay as $day => $daySchedules) {
                $day = (int)$day;
                $dayShifts = [];
                $dailySporHours = 0;
    
                $fullDate = Carbon::createFromFormat('Y-m-d', $date->format('Y') . '-' . $date->format('m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT));
                $isWeekend = $fullDate->isWeekend();
    
                if ($daySchedules->count() > 1) {
                    foreach ($daySchedules as $schedule) {
                        $hours = round($schedule->total_minutes / 60);
                        
                        if ($schedule->schedule_status_id === 1) {
                            // Use display code if available, otherwise use hours
                            $shiftDisplay = $schedule->display_code ?: $hours;
                            $dayShifts[] = $shiftDisplay;
                            $monthData['totalHours'] += $hours;
                            $dailySporHours += $hours;
                        } else {
                            $statusCode = $schedule->scheduleStatus->code;
                            $dayShifts[] = $hours . $statusCode;
                            
                            if (in_array($statusCode, $daySpecificCodes)) {
                                $daySpecificStatuses[] = $statusCode . sprintf("%02d", $day);
                            } else if ($statusCode !== 'PREZ') {
                                if (!isset($statusSummary[$statusCode])) {
                                    $statusSummary[$statusCode] = 0;
                                }
                                $statusSummary[$statusCode] += $hours;
                            }
    
                            if (in_array($statusCode, $sporStatusCodes)) {
                                $dailySporHours += $hours;
                            }
                        }
                    }
    
                    if (!$isWeekend) {
                        $monthData['spor'] += min($dailySporHours, $maxDailySpor);
                    }
    
                    if (!empty($dayShifts)) {
                        $monthData['hours'][$day] = implode("+\n", $dayShifts);
                    }
                } else {
                    $schedule = $daySchedules->first();
                    $hours = round($schedule->total_minutes / 60);
                    $statusCode = $schedule->scheduleStatus->code;
    
                    if ($schedule->schedule_status_id === 1) {
                        // Use display code if available, otherwise use hours
                        $monthData['hours'][$day] = $schedule->display_code ?: $hours;
                        $monthData['totalHours'] += $hours;
                        if (in_array('PREZ', $sporStatusCodes) && !$isWeekend) {
                            $monthData['spor'] += min($hours, $maxDailySpor);
                        }
                    } else {
                        $monthData['hours'][$day] = $statusCode;
                        
                        if (in_array($statusCode, $daySpecificCodes)) {
                            $daySpecificStatuses[] = $statusCode . sprintf("%02d", $day);
                        } else if ($statusCode !== 'PREZ') {
                            if (!isset($statusSummary[$statusCode])) {
                                $statusSummary[$statusCode] = 0;
                            }
                            $statusSummary[$statusCode] += $hours;
                        }
    
                        if (in_array($statusCode, $sporStatusCodes) && !$isWeekend) {
                            $monthData['spor'] += min($hours, $maxDailySpor);
                        }
                    }
                }
            }
    
            // Build details array with dates for sorting
            $details = [];
    
            // Add regular status summaries first
            foreach ($statusSummary as $code => $hours) {
                if ($hours > 0) {
                    $details[] = [
                        'sort_date' => 0,
                        'text' => "{$code}: {$hours} ore"
                    ];
                }
            }
    
            // Add day-specific statuses with their dates for sorting
            foreach ($daySpecificStatuses as $status) {
                $code = substr($status, 0, -2);
                $day = (int)substr($status, -2);
                
                $details[] = [
                    'sort_date' => $day,
                    'text' => $code . '<sup>' . sprintf("%02d", $day) . '</sup>'
                ];
            }
    
            // Sort by date
            usort($details, function($a, $b) {
                return $a['sort_date'] <=> $b['sort_date'];
            });
    
            // Extract just the text for final output
            $monthData['details'] = array_map(function($detail) {
                return $detail['text'];
            }, $details);
    
            $processedData[] = $monthData;
        }
    
        return $processedData;
    }

    private function calculateWorkingDays(Carbon $date)
    {
        $workingDays = 0;
        $daysInMonth = $date->daysInMonth;
        $year = $date->year;
        $month = $date->month;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = Carbon::createFromDate($year, $month, $day);
            
            // Skip weekends
            if ($currentDate->isWeekday()) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function formatHoursDetails($details)
    {
        if (empty($details)) {
            return '';
        }

        return collect($details)
            ->map(function ($detail) {
                $parts = explode(':', $detail);
                $code = trim($parts[0]);
                $hours = trim($parts[1]);
                return sprintf('%s: %s', $code, $hours);
            })
            ->join(', ');
    }
}