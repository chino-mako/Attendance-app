<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_clock_in_after_clock_out_validation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('attendance.request.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '19:00',
                'requested_clock_out' => '18:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['requested_clock_in']);
    }

    public function test_break_start_after_clock_out_validation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('attendance.request.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '19:00',
                'requested_break_end' => '19:30',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['requested_break_start']);
    }

    public function test_break_end_after_clock_out_validation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('attendance.request.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_break_start' => '17:00',
                'requested_break_end' => '19:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['requested_break_end']);
    }

    public function test_note_required_validation()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('attendance.request.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors(['note']);
    }

    public function test_successful_request_submission_and_visibility_for_user_and_admin()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 修正申請の登録
        $response = $this->actingAs($user)
            ->post(route('attendance.request.store'), [
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '08:00',
                'requested_clock_out' => '17:00',
                'note' => '修正申請テスト',
            ]);

        $response->assertRedirect(route('attendance.index'));

        // ユーザーの承認待ち修正申請一覧に表示されているか
        $this->actingAs($user)
            ->get(route('attendance.request.index'))
            ->assertSee('承認待ち');

        // 管理者の承認待ち修正申請一覧に表示されているか
        $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list'))
            ->assertSee('承認待ち');
    }

    public function test_detail_link_navigates_to_correct_screen()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => '承認待ち', // DBの値に合わせてください（'pending'等）
        ]);

        // ユーザー側一覧で詳細リンクが表示されているか
        $this->actingAs($user)
            ->get(route('attendance.request.index'))
            ->assertSee(route('attendance.request.show', $request->id));

        // 管理者側一覧で詳細リンクが表示されているか
        $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.list'))
            ->assertSee(route('admin.stamp_correction_request.show', $request->id));
    }
}
