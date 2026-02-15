<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Relations\HasMany;  
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\Auth;


class Categories extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    public $timestamps = false;

    protected $fillable = [
        'name',
        'icon', 
        'max_user_signups_per_period',
	
       
    ];

     

     public function gymClasses()
     {
         return $this->hasMany(GymClass::class, 'id_categories');
     }
}
