<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public int $clinicId, public string $type = 'update')
    {
        //
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('queue-updates.'.$this->clinicId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
        ];
    }
}
