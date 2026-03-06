@php
    $settings = app(App\Settings\AppearanceSettings::class);
    $bgColor = $settings->app_color ?? '#4f46e5';
@endphp
<x-app-layout> {{-- O el layout que uses para clientes --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-400 leading-tight mx-auto text-center flex justify-center items-center h-20 pt-6">
            {{ __('Mis Clases Inscritas') }}
        </h2>
    </x-slot>

    <div class="py-3">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-table-bg-color overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-400 ">

                     {{-- Mensajes de sesión (para éxito/error al anular) --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if (session('info')) {{-- Para mensajes informativos --}}
                        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                            {{ session('info') }}
                        </div>
                    @endif
            
                    <ul class="space-y-4">
                        {{-- Iterar sobre las inscripciones ($userSignups) pasadas desde el controlador --}}
                        {{-- Cada $signup contiene la información de la clase en $signup->gymClass --}}
                        @forelse ($userSignups as $signup)
                            {{-- Accedemos a la información de la clase a través de la relación --}}
                            @php $class = $signup->gymClass; @endphp

                            {{-- Solo mostrar si la clase existe (por si acaso) --}}
                            @if($class)
                                <li class=" p-4 rounded-md shadow-sm border border-custom-dark-gray flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                    {{-- Detalles de la clase --}}
                                    <div class="flex-grow">
                                        {{-- Mostramos el día de la semana --}}
                                        @php
                                            $dayNames = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                        @endphp
                                        <p class="text-sm text-gray-400  font-medium">
                                            {{ $dayNames[$class->day_of_week] ?? 'Día N/D' }}
                                        </p>
                                        <p class="font-semibold text-lg text-gray-400">
                                            {{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($class->start_time)->addMinutes($class->duration_minutes)->format('H:i') }}
                                        </p>
                                        <p class="text-xl text-gray-400 font-medium">{{ $class->name }}</p>
                                        <p class="text-sm text-gray-400">{{ $class->category->name ?? 'Sin categoría' }}</p>
                                        <p class="text-sm text-gray-400">Instructor: {{ $class->instructor->name ?? 'N/A' }}</p>
                                    </div>
                                    {{-- Botón/Formulario para Anular Inscripción --}}
                                    <div class="flex-shrink-0 w-full md:w-auto">
                                        {{-- Aquí irá el formulario para anular la inscripción ($signup->id) --}}
                                        {{-- Necesitaremos una ruta DELETE para 'inscripciones.destroy' --}}
                                        <form action="{{ route('inscripciones.destroy', $signup->id) }}" method="POST" onsubmit="return confirm('¿Seguro que quieres anular tu inscripción a esta clase?');">
                                            @csrf
                                            @method('DELETE')
                                  
                                            <button type="submit" class="w-full md:w-auto inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 disabled:opacity-50"
                                                    {{-- Deshabilitar si la clase ya pasó (lógica simple) --}}
                                                 @if(\Carbon\Carbon::parse($class->start_time)->isPast()) disabled @endif >
                                                Anular Inscripción
                                            </button>
                                        </form>
                                    </div>
                                </li>
                            @endif
                        @empty
                            {{-- Mensaje si no está inscrito a ninguna clase --}}
                            <li class="text-center py-8 text-gray-400">
                                Aún no te has apuntado a ninguna clase.
                                <a href="{{ route('schedule.today') }}" style="color:{{}};">Ver clases de hoy</a>
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
