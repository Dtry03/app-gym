<?php

namespace App\Http\Controllers;

use App\Models\GymClass; // Modelo de Clase
use App\Models\Signup;   // Modelo de Inscripción
use App\Models\Categories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado
use Illuminate\Support\Facades\Log;   // Para registrar errores
use Illuminate\Database\QueryException; // Para errores de BBDD
use Exception; // Para capturar excepciones generales
use Illuminate\Auth\Access\AuthorizationException; // Para errores de autorización
use Carbon\Carbon;
class SignupController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Guarda una nueva inscripción (apunta a un usuario a una clase).
     *
     * @param Request $request
     * @param GymClass $gymClass La clase a la que se quiere apuntar (inyectada por Route Model Binding)
     */
    public function store(Request $request, GymClass $gymClass)
    {
                $now= Carbon::now();
                $effectiveDate = ($now->hour >=21) ? $now->copy()->addDay() : $now->copy();
                $sessionStart= Carbon::parse($effectiveDate->toDateString().' '.$gymClass->start_time);
                $user = Auth::user();

        // --- Comprobaciones ---
        $alreadySignedUp = Signup::where('id_user', $user->id)
                                ->where('id_class', $gymClass->id)
                                ->exists();

        if ($alreadySignedUp) {
            // --- Redirección corregida ---
            return redirect()->route('schedule.today')->with('error', 'Ya estás apuntado a esta clase.');
        }

        $currentSignupsCount = Signup::where('id_class', $gymClass->id)->count();

        if ($currentSignupsCount >= $gymClass->capacity) {
             // --- Redirección corregida ---
            return redirect()->route('schedule.today')->with('error', 'Lo sentimos, la clase está completa.');
        }

        $category = $gymClass->category; // Obtener la categoría de la clase actual

        if ($category && $category->max_user_signups_per_period !== null && $category->max_user_signups_per_period > 0) {
            // Obtener los IDs de todas las clases que pertenecen a esta categoría
            $classIdsInThisCategory = Categories::find($category->id)
                                            ->gymClasses() // Usa la relación definida en el modelo Category
                                            ->pluck('id'); // Obtiene solo los IDs de las clases

            // Contar a cuántas clases de ESTA categoría ya está apuntado el usuario
            $userSignupsInThisCategoryCount = Signup::where('id_user', $user->id)
                                                  ->whereIn('id_class', $classIdsInThisCategory)
                                                  ->count();

            if ($userSignupsInThisCategoryCount >= $category->max_user_signups_per_period) {
                return redirect()->route('schedule.today')
                                 ->with('error', 'Has alcanzado el límite de ' . $category->max_user_signups_per_period . ' clases para la categoría "' . $category->name . '".');
            }
        }

        // --- Crear inscripción ---
        try {
            Signup::create([
                'id_user' => $user->id,
                'id_class' => $gymClass->id,
            ]);

             // --- Redirección corregida ---
            return redirect()->route('schedule.today')->with('success', '¡Te has apuntado a la clase "' . $gymClass->name . '" correctamente!');

        } catch (QueryException $e) {
            Log::error('Error de BBDD al inscribir usuario ' . $user->id . ' a clase ' . $gymClass->id . ': ' . $e->getMessage());
             // --- Redirección corregida ---
            return redirect()->route('schedule.today')->with('error', 'Hubo un error al procesar tu inscripción (Error BBDD).');
        } catch (Exception $e) {
            Log::error('Error inesperado al inscribir usuario ' . $user->id . ' a clase ' . $gymClass->id . ': ' . $e->getMessage());
             // --- Redirección corregida ---
            
    }
}

    /**
     * Remove the specified resource from storage.
     */

    public function destroy(Signup $signup)
    {
        try {

            $user= Auth::user();

            if($user->role !== 'admin' && $signup->id_user !== $user->id){
                throw new AuthorizationException('no tienes permiso para anular esta inscripción.');
            }

            $gymClass = $signup->gymClass;

            $now= Carbon::now();
            $format = strlen($gymClass->start_time) === 5 ? 'H:i' : 'H:i:s';
            $classTime= Carbon::createFromFormat('H:i', $gymClass->start_time);

            $classStartToday= Carbon::today()->setTime(
                    $classTime->hour,
                    $classTime->minute,
                    0
            );

            if($now->isoWeekday()== (int)$gymClass->day_of_week && $now->gte($classStartToday)){
                return redirect()->route('client.classes')->with('error', 'No puede cancelar la clase pasado el plazo de inscripción');
            }
            
            if($now->isoWeekday() > (int)$gymClass->day_of_week){
                return redirect()->route('client.classes')->with('error', 'No puede cancelar la clase pasado el plazo de inscripción');
            }
            


         

            $signup->delete();

            return redirect()->route('client.classes')->with('success', 'Inscricpción anulada correctamente');


        
        } catch (AuthorizationException $e) {
             Log::warning('Intento no autorizado de anular inscripción: User ID ' . Auth::id() . ', Signup ID ' . $signup->id);
             return redirect()->route('client.classes')->with('error', $e->getMessage());
        } catch (Exception $e) {
            // Capturar cualquier otro error inesperado
            Log::error('Error inesperado al anular inscripción: Signup ID ' . $signup->id . ' - ' . $e->getMessage());
            return redirect()->route('client.classes')
                             ->with('error', 'Error inesperado al anular la inscripción.');
        }
    }
}
