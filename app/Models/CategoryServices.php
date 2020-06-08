<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CategoryServices extends Model
{
    //
    use HasTranslations;

    public $translatable = ['title'];

    protected $table = 'categories_services';

}
