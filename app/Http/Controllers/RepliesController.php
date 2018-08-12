<?php

namespace App\Http\Controllers;

use App\Reply;
use App\Thread;

class RepliesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store($channel_id, Thread $thread)
    {
        $this->validate(request(), ['body' => 'required']);

        $reply = $thread->addReply([
            'body'    => request('body'),
            'user_id' => auth()->id(),
        ]);

        if (request()->expectsJson())
            return $reply->load('owner');

        return back()->with('flash', 'Your reply has been left.');
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

    /**
     * @param Reply $reply
     */
    public function update(Reply $reply)
    {
        $this->authorize('update', $reply);

        $reply->update(request(['body']));
    }
}
