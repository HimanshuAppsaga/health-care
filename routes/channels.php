<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('queue-updates.{clinicId}', function ($user, $clinicId) {
    return (int) $user->clinic_id === (int) $clinicId;
});
