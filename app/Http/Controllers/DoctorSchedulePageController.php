<?php

namespace App\Http\Controllers;

use App\Models\DoctorSchedule;
use Inertia\Inertia;
use Inertia\Response;

class DoctorSchedulePageController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('doctor/Schedule', [
            'schedules' => DoctorSchedule::with(['doctor.specialization', 'poli', 'room'])
                ->orderBy('day')
                ->get(),
        ]);
    }
}
