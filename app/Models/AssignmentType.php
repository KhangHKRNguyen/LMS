<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentType extends Model
{
    use HasFactory;

    protected $table = 'assignment_types';

    protected $fillable = ['name'];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'assignment_type_id');
    }
}