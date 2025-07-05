<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_see_own_attendance_records()
    {
        $user = User::factory()->create();
        $attendances = Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.index'));

        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date);
        }
    }

    public function test_current_month_is_displayed_on_attendance_index()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('attendance.index'));

        $response->assertStatus(200);
        // ビューの月表記が「2025年7月」などの場合、n（0埋めなし）でチェック
        $response->assertSee(Carbon::now()->format('Y年n月'));
    }

    public function test_previous_month_data_is_displayed_when_selected()
    {
        $user = User::factory()->create();
        $previousMonth = Carbon::now()->subMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $previousMonth->copy()->startOfMonth()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.index', ['month' => $previousMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($previousMonth->format('Y年n月'));
        $response->assertSee($attendance->work_date);
    }

    public function test_next_month_data_is_displayed_when_selected()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->copy()->startOfMonth()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.index', ['month' => $nextMonth->format('Y-m')]));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y年n月'));
        $response->assertSee($attendance->work_date);
    }

    public function test_user_can_view_attendance_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($attendance->work_date);
    }
}
