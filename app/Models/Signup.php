<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Para relaciones (opcional)

class Signup extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * Laravel infiere 'signups' por defecto. Si tu tabla se llama diferente,
     * descomenta y ajusta la siguiente línea:
     * protected $table = 'nombre_tabla_inscripciones';
     */

    /**
     * Indicates if the model should be timestamped.
     * Ponlo en false si tu tabla 'signups' no tiene created_at/updated_at.
     *
     * @var bool
     */
    public $timestamps = false; // Ajusta según tu migración

    /**
     * The attributes that are mass assignable.
     * ¡IMPORTANTE! Asegúrate que los campos que intentas guardar con create() estén aquí.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_user',
        'id_class',
    ];

    /**
     * Get the user that owns the signup.
     * Relación opcional pero útil.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    /**
     * Get the class that the signup belongs to.
     * Relación opcional pero útil.
     */
    public function gymClass(): BelongsTo // O class() si tu modelo es GymClass
    {
        // Asegúrate que el modelo de clase se llame GymClass o ajústalo
        // y que la clave foránea sea id_class
        return $this->belongsTo(GymClass::class, 'id_class');
    }
}
