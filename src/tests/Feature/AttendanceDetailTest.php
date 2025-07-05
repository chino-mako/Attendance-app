<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_detail_displays_user_name()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_attendance_detail_displays_selected_date()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-07-04',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2025-07-04');
    }

    public function test_attendance_detail_displays_clock_in_and_out_times()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        // ビューで秒が非表示の場合は秒なしの時刻で判定
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_attendance_detail_displays_break_times()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00:00',
            'break_end' => '12:45:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/{$attendance->id}");

        $response->assertStatus(200);

        // 同様に秒なしでの表示を想定
        $response->assertSee('12:00');
        $response->assertSee('12:45');
    }
}
