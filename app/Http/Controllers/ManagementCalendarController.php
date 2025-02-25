<?php

namespace App\Http\Controllers;

use App\Data\DayLimitData;
use App\Models\DayLimit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ManagementCalendarController extends Controller
{

   public function index()
	{

        $dayLimit = new DayLimit();
        $startDayMonth = now()->startOfMonth();
        $endDayMonth = now()->endOfMonth();

        $event = $dayLimit
            ->whereBetween('start', [$startDayMonth, $endDayMonth])
            ->get();

        $dayLimits = DayLimitData::collection($event);

        return Inertia::render('ManagementCalendar')
        ->with([
            'dayLimits' => $dayLimits
        ]);
	}

    public function addEvent(Request $request)
    {

        ray($request->input());

        $messages = [
            'required' => 'Câmpul :attribute este obligatoriu.',
        ];

        $attributes = [
            'eventId' => 'Record id',
            'formAction' => 'Actiune form',
            'eventName' => 'Nume eveniment',
            'dateStart' => 'Data începere eveniment',
            'dateEnd' => 'Data finalizare eveniment',
        ];

        $validator = Validator::make($request->all(), [
            'formAction' => ['required'],
            'eventId' => ['required'],
            'eventName' => ['required'],
            'dateStart' => ['required'],
            'dateEnd' => ['required'],
        ], $messages, $attributes);

        //validat period overlaps
        $validator->after(function ($validator) use ($request){

            if($request->formAction == 'add'){
                $start = (new Carbon($request->dateStart));
                $end = (new Carbon($request->dateEnd));

                $dayLimit = new DayLimit();
                $events = $dayLimit
                    ->whereBetween('start', [$start, $end])
                    ->get();

                ray($events);

                if($events->count() > 0){
                    $validator->errors()->add(
                        'dateStart', 'Perioada selectată se suprapune peste un eveniment deja introdus !'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
        }

        //action to add event
        if($request->formAction == 'add'){
            $dayLimit = new DayLimit();
            $dayLimit->name = $request->eventName;
            $dayLimit->start = (new Carbon($request->dateStart))->format("Y-m-d H:i:s");
            $dayLimit->finish = (new Carbon($request->dateEnd))->format("Y-m-d H:i:s");
            $dayLimit->save();
        }

        //action to delete event
        if($request->formAction == 'delete'){
            ray('Delete received record');
            ray($request->eventId);

            $dayLimit = new DayLimit();
            $dayLimit = $dayLimit->whereId($request->eventId)->first();
            if($dayLimit){
                $dayLimit->delete();
            }
        }

        return to_route('management-calendar');
    }
}
