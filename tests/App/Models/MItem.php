<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class MItem extends Model
{
    protected $table = 'm_items';
    public $timestamps = false;
    protected $guarded = [];
}
