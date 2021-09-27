<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonsUniqueField extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persons_unique_fields';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'alias',
        'name'
    ];

    public function data(): HasMany
    {
        return $this->hasMany(PersonsData::class, 'field_id');
    }
}
