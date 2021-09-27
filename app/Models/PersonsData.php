<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonsData extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persons_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'field_id',
        'value'
    ];

    /**
     * @return BelongsTo
     */
    public function uniqueField(): BelongsTo
    {
        return $this->belongsTo(PersonsUniqueField::class, 'field_id');
    }
}
