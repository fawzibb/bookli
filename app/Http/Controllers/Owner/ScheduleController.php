<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BlockedTime;
use App\Models\WeeklySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $days = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        $weeklySchedules = WeeklySchedule::where('business_id', $businessId)
            ->orderBy('day_of_week')
            ->get()
            ->keyBy('day_of_week');

        $recurringBlockedTimes = BlockedTime::where('business_id', $businessId)
            ->where('is_recurring', true)
            ->orderBy('day_of_week')
            ->get();
        $groupedRecurringBlockedTimes = $recurringBlockedTimes
            ->groupBy(function ($item) {
                return $item->start_time . '|' . $item->end_time . '|' . $item->reason;
            });

        $datedBlockedTimes = BlockedTime::where('business_id', $businessId)
            ->where('is_recurring', false)
            ->orderByDesc('blocked_date')
            ->get();

        return view('owner.schedules.index', compact(
            'days',
            'weeklySchedules',
            'recurringBlockedTimes',
            'datedBlockedTimes',
            'groupedRecurringBlockedTimes'
        ));
    }
    public function destroyGroup(Request $request)
{
    $businessId = Auth::guard('business')->user()->business_id;

    BlockedTime::where('business_id', $businessId)
        ->where('is_recurring', true)
        ->where('start_time', $request->start_time)
        ->where('end_time', $request->end_time)
        ->where('reason', $request->reason)
        ->delete();

    return back()->with('success', 'Blocked time deleted.');
}

    public function updateWeekly(Request $request)
{
    $businessId = Auth::guard('business')->user()->business_id;

    $request->validate([
        'schedule_type' => ['required', 'in:same_all_days,custom_days'],
        'same_open_time' => ['nullable'],
        'same_close_time' => ['nullable'],
        'same_is_24_hours' => ['nullable'],
        'off_days' => ['nullable', 'array'],
        'days' => ['nullable', 'array'],
    ]);

    if ($request->schedule_type === 'same_all_days') {
        $offDays = $request->off_days ?? [];

        for ($day = 0; $day <= 6; $day++) {
            $isOff = in_array((string) $day, $offDays);

            $openTime = null;
            $closeTime = null;

            if (!$isOff) {
                if ($request->has('same_is_24_hours')) {
                    $openTime = '00:00';
                    $closeTime = '23:59';
                } else {
                    $openTime = $request->same_open_time;
                    $closeTime = $request->same_close_time;
                }
            }

            WeeklySchedule::updateOrCreate(
                [
                    'business_id' => $businessId,
                    'day_of_week' => $day,
                ],
                [
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_off' => $isOff,
                ]
            );
        }

        return back()->with('success', 'Weekly schedule updated.');
    }

    foreach ($request->days ?? [] as $day => $data) {
        $isOff = isset($data['is_off']);

        $openTime = null;
        $closeTime = null;

        if (!$isOff) {
            if (isset($data['is_24_hours'])) {
                $openTime = '00:00';
                $closeTime = '23:59';
            } else {
                $openTime = $data['open_time'] ?? null;
                $closeTime = $data['close_time'] ?? null;
            }
        }

        WeeklySchedule::updateOrCreate(
            [
                'business_id' => $businessId,
                'day_of_week' => $day,
            ],
            [
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'is_off' => $isOff,
            ]
        );
    }

    return back()->with('success', 'Weekly schedule updated.');
}
    public function storeRecurring(Request $request)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        $request->validate([
            'apply_to' => ['required', 'in:one_day,all_working_days'],
            'day_of_week' => ['nullable', 'integer'],
            'start_time' => ['nullable'],
            'end_time' => ['nullable'],
            'reason' => ['nullable'],
        ]);

        $create = function ($day) use ($request, $businessId) {
            BlockedTime::firstOrCreate(
                [
                    'business_id' => $businessId,
                    'day_of_week' => $day,
                    'is_recurring' => true,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'full_day' => false,
                ],
                [
                    'reason' => $request->reason,
                ]
            );
        };

        if ($request->apply_to === 'one_day') {
            $create($request->day_of_week);
        } else {
            $days = WeeklySchedule::where('business_id', $businessId)
                ->where('is_off', false)
                ->pluck('day_of_week');

            foreach ($days as $day) {
                $create($day);
            }
        }

        return back()->with('success', 'Recurring blocked time added.');
    }

public function storeDate(Request $request)
{
    $businessId = Auth::guard('business')->user()->business_id;

    $request->validate([
        'blocked_date' => ['required', 'date'],
        'start_time' => ['nullable', 'required_unless:full_day,1'],
        'end_time' => ['nullable', 'required_unless:full_day,1'],
        'reason' => ['nullable', 'string', 'max:255'],
    ]);

    $isFullDay = $request->boolean('full_day');

    BlockedTime::create([
        'business_id' => $businessId,
        'blocked_date' => $request->blocked_date,
        'day_of_week' => null,
        'is_recurring' => false,
        'start_time' => $isFullDay ? null : $request->start_time,
        'end_time' => $isFullDay ? null : $request->end_time,
        'full_day' => $isFullDay,
        'reason' => $request->reason,
    ]);

    return back()->with('success', 'Date blocked time added.');
}

    public function destroy(BlockedTime $blockedTime)
    {
        $businessId = Auth::guard('business')->user()->business_id;

        abort_if($blockedTime->business_id !== $businessId, 403);

        $blockedTime->delete();

        return back()->with('success', 'Blocked time deleted.');
    }
}