<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Event;
use Illuminate\Support\Facades\Schedule;
use Tests\TestCase;

class ScheduledTasksTest extends TestCase
{
    public function test_queue_worker_is_scheduled_every_minute(): void
    {
        $this->artisan('schedule:list')->assertSuccessful();

        $queueEvent = collect(Schedule::events())->first(
            fn (Event $event): bool => str_contains((string) $event->command, 'queue:work --stop-when-empty --max-time=55'),
        );

        $this->assertNotNull($queueEvent);
        $this->assertSame('* * * * *', $queueEvent->expression);
        $this->assertTrue($queueEvent->withoutOverlapping);
    }
}
