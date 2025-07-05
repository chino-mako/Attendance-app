<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance', ['action' => 'clock_in']);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);
    }

    public function test_user_can_clock_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'status' => '出勤中',
            'clock_in' => now(),
        ]);

        $response = $this->post('/attendance', ['action' => 'clock_out']);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => '退勤済',
        ]);
    }
}
