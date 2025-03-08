<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Laravolt\Suitable\AutoFilter;
use Laravolt\Suitable\AutoSearch;
use Laravolt\Suitable\AutoSort;

class Project extends Model
{
    use HasUlids;
    use AutoSort;
    use AutoFilter;
    use AutoSearch;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'subtitle' => 'array',
        '_subtitle' => 'array',
    ];
}
