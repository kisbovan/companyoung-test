<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persons';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'field_id',
        'value'
    ];

    public function data(): HasMany
    {
        return $this->hasMany(PersonsData::class, 'person_id');
    }
}
