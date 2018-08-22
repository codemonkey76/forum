<?php

namespace App\Http\Forms;

use App\Notifications\YouWereMentioned;
use App\Reply;
use App\User;
use Gate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class CreatePostForm extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('create', new Reply);
    }

    protected function failedAuthorization()
    {
        throw new ThrottleRequestsException('You are posting too frequently, please take a break.');
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'body' => 'required|spamfree',
        ];
    }

    public function persist($thread)
    {
        $reply =  $thread->addReply([
            'body'    => request('body'),
            'user_id' => auth()->id(),
        ]);

        preg_match_all('/\@([^\s\.]+)/',$reply->body, $matches);

        foreach ($matches[1] as $name)
        {
            $user = User::whereName($name)->first();
            if ($user) $user->notify(new YouWereMentioned($reply));
        }

        return $reply->load('owner');
    }
}
