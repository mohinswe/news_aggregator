<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{

    protected $fillable = [
        'source_id',
        'article_id',
        'title',
        'description',
        'keywords',
        'snippet',
        'language',
        'published_at',
        'author_or_source',
        'categories',
        'url',
        'image_url',
        'status',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'categories' => 'array',
    ];

}
