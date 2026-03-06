<?php

namespace App\Http\Controllers;

use App\Models\GymClass; // Modelo de Clase
use App\Models\Signup;   // Modelo de Inscripción
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario
use Carbon\Carbon; // Para manejar fechas y horas

class ScheduleController extends Controller
{
    /**
     * Display the weekly schedule (informational).
     * Muestra el horario semanal informativo.
     */
    public function index()
    {
        // Obtener todas las clases con relaciones, ordenadas por día y luego por hora
        $allClasses = GymClass::with(['category', 'instructor'])
                            ->orderBy('day_of_week') // Ordenar primero por día (1-7)
                            ->orderBy('start_time')  // Luego por hora
                            ->get();

        // Agrupar las clases por día de la semana (la clave será 1, 2, ..., 7)
        $classesGroupedByDay = $allClasses->groupBy('day_of_week');

        // Nombres de los días para la cabecera de la tabla/grid (usando 1-7 como claves)
        $dayNames = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo',
        ];

        // Pasar los datos agrupados y los nombres de los días a la vista
        return view('schedule.index', [
            'classesGroupedByDay' => $classesGroupedByDay,
            'dayNames' => $dayNames
            ]);
    }

    /**
     * Display the classes for the effective day (today or tomorrow after 9 PM)
     * Muestra las clases para el día efectivo (hoy, o mañana si > 21:00) con opción de apuntarse.
     */
    public function todayClasses()
    {
        // Determinar la fecha efectiva (hoy o mañana después de las 21:00)
        $now = Carbon::now();
        // Considera la zona horaria si es relevante para tu aplicación
        // $now = Carbon::now(config('app.timezone'));
        $effectiveDate = ($now->hour >= 21) ? $now->copy()->addDay() : $now; // Usar copy() para no modificar $now
        $effectiveDateStart=$effectiveDate->copy()->startOfDay();
        $effectiveDateEnd= $effectiveDate->copy()->endOfDay();

        // Obtener el día de la semana ISO (1=Lunes, 7=Domingo) para la fecha efectiva
        $effectiveDayOfWeek = $effectiveDate->dayOfWeekIso;

        // Nombres de los días
        $dayNames = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
        $effectiveDayName = $dayNames[$effectiveDayOfWeek] ?? 'Día Desconocido';

        // Obtener las clases para el día efectivo, cargando relaciones necesarias
        // Incluimos 'signups' para poder contar cuántos hay apuntados y verificar si el user está apuntado
        $todaysClasses = GymClass::with(['category', 'instructor', 'signups']) // Carga ansiosa de signups
                                ->where('day_of_week', $effectiveDayOfWeek) // Filtra por el día efectivo
                                ->orderBy('start_time')
                                ->get();

        // Obtener los IDs de las clases a las que el usuario actual YA está apuntado
        // Solo si el usuario está logueado
        $userSignupIds = collect();
        if (Auth::check()) {
            $userSignupIds = Signup::where('id_user', Auth::id())
                                   ->pluck('id_class') // Obtiene solo la columna id_class
                                   ->flip(); // Convierte los valores en claves para búsqueda rápida (ej: [class_id => 0])
        }


        // Pasar los datos a la vista 'schedule.today'
        return view('schedule.today', [
            'todaysClasses' => $todaysClasses,
            'effectiveDayName' => $effectiveDayName,
            'userSignupIds' => $userSignupIds, // Pasamos los IDs de las inscripciones del usuario
        ]);
    }
}
