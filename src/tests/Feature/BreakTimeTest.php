<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => now()->toDateString(),
            'clock_in' => now()->subHours(2)->format('H:i:s'),
            'status' => '出勤中',
        ]);
    }

    public function test_user_can_start_break_and_status_changes_to_breaking()
    {
        $response = $this->actingAs($this->user)->post('/break/start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => '休憩中',
        ]);
    }

    public function test_user_can_start_and_end_break_multiple_times()
    {
        $this->actingAs($this->user)->post('/break/start');
        $this->actingAs($this->user)->post('/break/end');

        $response = $this->actingAs($this->user)->post('/break/start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => '休憩中',
        ]);
    }

    public function test_user_can_end_break_and_status_changes_to_working()
    {
        $this->actingAs($this->user)->post('/break/start');

        $response = $this->actingAs($this->user)->post('/break/end');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => '出勤中',
        ]);
    }

    public function test_break_end_button_can_be_pressed_multiple_times()
    {
        $this->actingAs($this->user)->post('/break/start');
        $this->actingAs($this->user)->post('/break/end');

        $this->actingAs($this->user)->post('/break/start');

        $response = $this->actingAs($this->user)->post('/break/end');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'id' => $this->attendance->id,
            'status' => '出勤中',
        ]);
    }

    public function test_break_time_is_recorded_in_attendance_summary()
    {
        $this->actingAs($this->user)->post('/break/start');

        // 可能なら Carbonのモックで時刻ずらしをする方が望ましいですが、
        // sleep(1)でも可
        sleep(1);

        $this->actingAs($this->user)->post('/break/end');

        $attendance = $this->attendance->fresh();

        $this->assertNotEmpty($attendance->breaks);
        $this->assertNotNull($attendance->breaks->first()->break_start);
        $this->assertNotNull($attendance->breaks->first()->break_end);
    }
}
