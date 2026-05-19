<?php

namespace Tests\Feature;

use App\Events\QueueUpdated;
use App\Events\ScheduleUpdated;
use App\Models\Clinic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastChannelTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_updated_resolves_clinic_api_key_for_broadcast_channel()
    {
        // 1. Create a clinic with a specific API key
        $clinic = Clinic::create([
            'name' => 'Broadcast Test Clinic',
            'api_key' => 'unique_test_broadcast_api_key_123',
            'address' => '123 Broadcast St.',
        ]);

        // 2. Instantiate QueueUpdated with this clinic's ID
        $event = new QueueUpdated($clinic->id, 'next');

        // 3. Assert the broadcastOn() returns the correct channel with the api_key
        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('queue-updates.unique_test_broadcast_api_key_123', $channels[0]->name);
    }

    public function test_schedule_updated_resolves_clinic_api_key_for_broadcast_channel()
    {
        // 1. Create a clinic with a specific API key
        $clinic = Clinic::create([
            'name' => 'Broadcast Test Clinic 2',
            'api_key' => 'unique_schedule_broadcast_api_key_456',
            'address' => '456 Broadcast Rd.',
        ]);

        // 2. Instantiate ScheduleUpdated with this clinic's ID
        $event = new ScheduleUpdated($clinic->id, 'update');

        // 3. Assert the broadcastOn() returns the correct channel with the api_key
        $channels = $event->broadcastOn();
        $this->assertCount(1, $channels);
        $this->assertEquals('schedule-updates.unique_schedule_broadcast_api_key_456', $channels[0]->name);
    }

    public function test_broadcast_channels_fallback_to_clinic_id_if_api_key_missing()
    {
        // 1. Create a clinic WITHOUT an API key
        $clinic = Clinic::create([
            'name' => 'Fallback Clinic',
            'api_key' => '',
            'address' => '789 Fallback Ave.',
        ]);

        // 2. Instantiate events
        $queueEvent = new QueueUpdated($clinic->id, 'next');
        $scheduleEvent = new ScheduleUpdated($clinic->id, 'update');

        // 3. Assert fallback to clinic ID
        $queueChannels = $queueEvent->broadcastOn();
        $this->assertEquals('queue-updates.'.$clinic->id, $queueChannels[0]->name);

        $scheduleChannels = $scheduleEvent->broadcastOn();
        $this->assertEquals('schedule-updates.'.$clinic->id, $scheduleChannels[0]->name);
    }
}
