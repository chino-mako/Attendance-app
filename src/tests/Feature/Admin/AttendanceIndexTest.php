<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->users = User::factory()->count(2)->create();

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::today()->format('Y-m-d'),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
            ]);
        }
    }

    public function test_管理者は当日の勤怠一覧を正しく確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list');

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }

        $response->assertSee(Carbon::today()->format('Y-m-d'));
    }

    public function test_管理者は前日の勤怠一覧を確認できる()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $yesterday,
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list?date=' . $yesterday);

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('10:00');
            $response->assertSee('19:00');
        }

        $response->assertSee($yesterday);
    }

    public function test_管理者は翌日の勤怠一覧を確認できる()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');

        foreach ($this->users as $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $tomorrow,
                'clock_in' => '08:30:00',
                'clock_out' => '17:30:00',
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get('/admin/attendance/list?date=' . $tomorrow);

        $response->assertStatus(200);

        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('08:30');
            $response->assertSee('17:30');
        }

        $response->assertSee($tomorrow);
    }
}
