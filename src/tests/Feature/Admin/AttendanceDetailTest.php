<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->user = User::factory()->create();
        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'work_date' => Carbon::today()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'note' => '通常勤務',
        ]);
    }

    public function test_勤怠詳細画面に表示される内容が正しい()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/{$this->attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('通常勤務');
    }

    public function test_出勤時間が退勤時間より後だとバリデーションエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['clock_in']);
    }

    public function test_休憩開始時間が退勤時間より後だとバリデーションエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '19:00',
                'break_end' => '19:30',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['break_start']);
    }

    public function test_休憩終了時間が退勤時間より後だとバリデーションエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_start' => '17:00',
                'break_end' => '19:00',
                'note' => 'テスト',
            ]);

        $response->assertSessionHasErrors(['break_end']);
    }

    public function test_備考欄が未入力だとバリデーションエラーになる()
    {
        $response = $this->actingAs($this->admin)
            ->put("/admin/attendance/{$this->attendance->id}", [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors(['note']);
    }
}
