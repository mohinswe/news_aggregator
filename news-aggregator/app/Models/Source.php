<?php

namespace App\Models;

use App\Contracts\SourceType;
use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'url',
        'api_key',
        'api_secret',
        'description',
        'image',
        'status',
    ];


    protected $casts = [
        'status'  => 'boolean',
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted'
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function type(): SourceType
    {
        $typeClass = config('core.source_types_class')[$this->slug];
        return new $typeClass($this);
    }

}


