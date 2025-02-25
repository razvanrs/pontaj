<?php

// RaportSituatiePrezentaZilnicaController.php

namespace App\Http\Controllers\Rapoarte;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class RaportSituatiePrezentaZilnicaController extends Controller
{
    public function index()
    {
        // Get business unit groups instead of business units
        $businessUnitGroups = \App\Models\BusinessUnitGroup::orderBy('sel_order')
            ->get()
            ->map(function($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name
                ];
            });

        return Inertia::render('Rapoarte/RaportSituatiePrezentaZilnica')
            ->with([
                'businessUnitGroups' => $businessUnitGroups,
                'stats' => [
                    'total' => 0,
                    'present' => 0,
                    'onDuty' => 0
                ],
                'presentEmployees' => [],
                'onDutyEmployees' => [],
                'absentEmployees' => [],
                'selectedDate' => now()->format('Y-m-d')
            ]);
    }

    public function getDailyData(Request $request)
    {
        try {
            $date = Carbon::parse($request->input('date'))->startOfDay();
            $businessUnitGroupId = $request->input('business_unit_group_id');

            // dd($businessUnitGroupId);
    
            // Get all business units in the selected group
            $businessUnitIds = \App\Models\BusinessUnit::where('business_unit_group_id', $businessUnitGroupId)
                ->pluck('id');

            // First, get the total number of employees in the business unit group
            $totalEmployees = Employee::whereHas('businessUnitEmployees', function($q) use ($businessUnitIds) {
                $q->whereIn('business_unit_id', $businessUnitIds);
            })->count();
    
            // Base query for schedules
            $query = EmployeeSchedule::with(['employee.businessUnitEmployees', 'scheduleStatus', 'employee.militaryRank'])
                ->whereDate('date_start', '<=', $date)
                ->whereDate('date_finish', '>=', $date)
                ->whereHas('employee', function($q) use ($businessUnitIds) {
                    $q->whereHas('businessUnitEmployees', function($q) use ($businessUnitIds) {
                        $q->whereIn('business_unit_id', $businessUnitIds);
                    });
                });
    
            $schedules = $query->get();
    
            // Initialize arrays
            $present = [];
            $onDuty = [];
            $absent = [];
    
            foreach ($schedules as $schedule) {

                if (!$schedule->employee) continue;                
    
                $employeeData = [
                    'name' => $schedule->employee->full_name,
                    'military_rank' => $schedule->employee->militaryRank ?? '',
                    'status' => $schedule->scheduleStatus->code ?? '',
                    'hours' => Carbon::parse($schedule->date_start)->format('H:i') . '-' . 
                              Carbon::parse($schedule->date_finish)->format('H:i')
                ];
    
                // Sort based on schedule status
                switch ($schedule->schedule_status_id) {
                    case 1: // Present
                        $present[] = $employeeData;
                        break;
                    case 2: // On duty
                    case 3: // Special assignment
                        $onDuty[] = $employeeData;
                        break;
                    default:
                        $absent[] = $employeeData;
                        break;
                }
            }
    
            // Calculate statistics
            $stats = [
                'total' => $totalEmployees,
                'present' => count($present),
                'onDuty' => count($onDuty)
            ];
    
            return response()->json([
                'stats' => $stats,
                'present' => $present,
                'onDuty' => $onDuty,
                'absent' => $absent
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
