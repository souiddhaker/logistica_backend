<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SubService extends Model
{

    use HasTranslations;

    public $translatable = ['label'];
    protected $table = 'subservices';

    //

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at','pivot'
    ];

    public function service(){
        $this->belongsTo(Service::class);
    }


}
