<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_button_is_visible_and_works_for_user_not_yet_clocked_in()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance/clock-in');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_clock_in_button_is_not_visible_after_clock_out()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => now()->subHours(8)->format('H:i:s'),
            'clock_out' => now()->format('H:i:s'),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤'); // 「出勤」ボタンが表示されていないことを確認
    }

    public function test_clock_in_time_is_recorded_and_visible_on_admin_view()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_in->format('H:i'));
    }

    public function test_clock_out_button_is_visible_and_works()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => now()->subHours(4)->format('H:i:s'),
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->actingAs($user)->post('/attendance/clock-out');
        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => '退勤済',
        ]);
    }

    public function test_clock_out_time_is_visible_on_admin_view()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $attendance = Attendance::where('user_id', $user->id)->first();

        $response->assertStatus(200);
        $response->assertSee($attendance->clock_out->format('H:i'));
    }
}
