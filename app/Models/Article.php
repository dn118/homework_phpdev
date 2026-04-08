<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_key',
        'title',
        'url',
        'source',
        'description',
        'published_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserArticlePreference::class);
    }
}
