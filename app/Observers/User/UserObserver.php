<?php

namespace App\Observers\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserObserver
{
    public function created(User $user)
    {
        $loggedInUser = Auth::user();
        $keyTags = $loggedInUser ? $loggedInUser->id : 'guest';

        Cache::forget('public_get_all_users_' . $keyTags);
        Cache::forget('public_user_by_penggerak_' . $keyTags);
        Cache::forget('user_role_1_' . $keyTags);
        Cache::forget('user_role_2_' . $keyTags);
    }

    public function updated(User $user)
    {
        $loggedInUser = Auth::user();
        $keyTags = $loggedInUser ? $loggedInUser->id : 'guest';

        Cache::forget('public_get_all_users_' . $keyTags);
        Cache::forget('public_user_by_penggerak_' . $keyTags);
        Cache::forget('user_role_1_' . $keyTags);
        Cache::forget('user_role_2_' . $keyTags);
    }
}
