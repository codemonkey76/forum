<?php

namespace App\Http\Controllers;

use App\Inspections\Spam;
use App\Reply;
use App\Thread;
use Exception;
use Gate;

class RepliesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except('index');
    }

    public function index($channelId, Thread $thread)
    {
        return $thread->replies()->paginate(20);
    }

    /**
     * @param $channel_id
     * @param Thread $thread
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\RedirectResponse
     */
    public function store($channel_id, Thread $thread)
    {
//        try {

        if (Gate::denies('create', new Reply)) {
            return response('You are posting too frequently. Please take a break :)', 429);
        }

        $this->authorize('create', new Reply);

        request()->validate(['body' => 'required|spamfree']);

        $reply = $thread->addReply([
            'body'    => request('body'),
            'user_id' => auth()->id(),
        ]);
//        } catch (Exception $e) {
//            return response('Sorry, your reply could not be saved at this time.', 422);
//        }

        return $reply->load('owner');
    }

    /**
     * @param Reply $reply
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->delete();

        if (request()->expectsJson()) {
            return response(['status' => 'Reply deleted']);
        }

        return back();
    }

    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        try {
            request()->validate(['body' => 'required|spamfree']);

            $reply->update(request(['body']));
        } catch (Exception $e) {
            return response('Sorry, your reply could not be saved at this time.', 422);
        }
    }
}
