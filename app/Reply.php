<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Purify\Facades\Purify;

class Reply extends Model
{
    use Favoritable, RecordsActivity;

    protected $guarded = [];
    protected $with = ['owner', 'favorites'];
    protected $appends = ['favoritesCount', 'isFavorited', 'isBest'];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($reply) {
            $reply->thread->increment('replies_count');
        });

        static::deleted(function ($reply) {
            $reply->thread->decrement('replies_count');
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class);
    }

    public function wasJustPublished()
    {
        return $this->created_at->gt(Carbon::now()->subMinute());
    }

    public function path()
    {
        return $this->thread->path() . "#reply-{$this->id}";
    }

    public function mentionedUsers()
    {
        preg_match_all('/(^|\W)(@\b([-a-zA-Z0-9._]{1,25}))\b/', $this->body, $matches);

        return $matches[3];
    }

    public function setBodyAttribute($body)
    {
        $this->attributes['body'] = preg_replace('/(^|\W)(@\b([-a-zA-Z0-9._]{1,25}))\b/', ' <a href="/profiles/$3">$2</a>', $body);
    }

    public function getBodyAttribute($body)
    {
        return Purify::clean($body);
    }

    public function isBest()
    {
        return $this->thread->best_reply_id==$this->id;
    }
    public function getIsBestAttribute()
    {
        return $this->isBest();
    }
}
