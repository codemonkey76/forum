<?php

namespace App;

use App\Events\ThreadHasNewReply;
use App\Events\ThreadReceivedNewReply;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Stevebauman\Purify\Facades\Purify;

class Thread extends Model
{
    use RecordsActivity, Searchable;

    protected $guarded = [];

    protected $with = ['creator', 'channel'];

    protected $appends = ['isSubscribedTo'];

    protected $casts = [
        'locked' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($thread) {
            $thread->replies->each->delete();
        });

        static::created(function ($thread) {
           $thread->update(['slug'=>$thread->title]);
        });

    }

    /**
     * @return string
     */
    public function path()
    {
        return "/threads/{$this->channel->slug}/{$this->slug}";
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function channel()
    {
        return $this->belongsTo('App\Channel');
    }

    /**
     * @param $reply
     * @return Model
     */
    public function addReply($reply)
    {
        $reply = $this->replies()
                      ->create($reply);

        event(new ThreadReceivedNewReply($reply));

        return $reply;
    }

    /**
     * @param $query
     * @param $filters
     * @return mixed
     */
    public function scopeFilter($query, $filters)
    {
        return $filters->apply($query);
    }

    public function subscribe($userId = null)
    {
        $this->subscriptions()->create([
            'user_id' => $userId ?: auth()->id(),
        ]);

        return $this;
    }

    public function unsubscribe($userId = null)
    {
        $this->subscriptions()
             ->where('user_id', $userId ?: auth()->id())
             ->delete();
    }

    public function subscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public function getIsSubscribedToAttribute()
    {
        return $this->subscriptions()
                    ->where('user_id', auth()->id())
                    ->exists();
    }

    public function hasUpdatesFor($user)
    {
        return $this->updated_at > cache($user->visitedThreadCacheKey($this));
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setSlugAttribute($value)
    {
        $slug     = str_slug($value);

        if (static::whereSlug($slug)->exists()) {
            $slug = "{$slug}-" . $this->id;
        }

        $this->attributes['slug'] = $slug;
    }

    public function getBodyAttribute($body)
    {
        return Purify::clean($body);
    }

    public function markBestReply(Reply $reply)
    {
        $this->update(['best_reply_id'=>$reply->id]);
    }

    public function toSearchableArray()
    {
        return $this->toArray() + ['path' => $this->path()];
    }
}
