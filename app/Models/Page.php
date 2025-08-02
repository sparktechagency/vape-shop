<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['type', 'content'];

    /**
     * Get the page content by type.
     *
     * @param string $type
     * @return string|null
     */

}
