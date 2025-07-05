<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $pendingRequest;
    protected $approvedRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);

        $user = User::factory()->create();

        // 承認待ちの修正申請
        $this->pendingRequest = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        // 承認済みの修正申請
        $this->approvedRequest = AttendanceRequest::factory()->create([
            'user_id' => $user->id,
            'status' => 'approved',
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);
    }

    public function test_承認待ちの修正申請が一覧に表示される()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee($this->pendingRequest->work_date);
    }

    public function test_承認済みの修正申請が一覧に表示される()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee($this->approvedRequest->work_date);
    }

    public function test_修正申請の詳細が正しく表示される()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/stamp_correction_request/approve/{$this->pendingRequest->id}");

        $response->assertStatus(200);
        $response->assertSee($this->pendingRequest->work_date);
    }

    public function test_修正申請が承認されると勤怠情報が更新される()
    {
        $data = [
            'status' => 'approved',
        ];

        $response = $this->actingAs($this->admin)
            ->post("/admin/stamp_correction_request/approve/{$this->pendingRequest->id}", $data);

        $response->assertRedirect('/admin/stamp_correction_request/list');

        $this->assertDatabaseHas('attendance_requests', [
            'id' => $this->pendingRequest->id,
            'status' => 'approved',
        ]);
    }
}
