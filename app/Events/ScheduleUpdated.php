<?php

namespace App\Events;

use App\Models\Clinic;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $clinicId, public string $type = 'update')
    {
        //
    }

    public function broadcastOn(): array
    {
        $apiKey = Clinic::where('id', $this->clinicId)->value('api_key') ?: $this->clinicId;

        return [
            new Channel('schedule-updates.'.$apiKey),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}
