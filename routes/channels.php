<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use App\Models\Livestream;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('role.{id}', function ($user, $id) {
    return $user->hasRole($id);
});

Broadcast::channel('page.{id}', function ($user, $id) {
    if ($user) {
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }
});

Broadcast::channel('livestream.{livestream}', function ($user, Livestream $livestream) {
    if ($user->can('chat', $livestream)) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }
});
