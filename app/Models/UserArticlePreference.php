<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserArticlePreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'article_id',
        'hidden_at',
        'favorited_at',
    ];

    protected function casts(): array
    {
        return [
            'hidden_at' => 'datetime',
            'favorited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
