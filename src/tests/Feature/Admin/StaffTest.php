<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);

        $this->users = User::factory()->count(3)->create(['is_admin' => false]);

        foreach ($this->users as $user) {
            // 当月
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::now()->format('Y-m-d'),
                'clock_in' => '09:00:00',
                'clock_out' => '18:00:00',
                'note' => '通常勤務',
            ]);

            // 前月
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::now()->subMonth()->format('Y-m-d'),
                'clock_in' => '10:00:00',
                'clock_out' => '19:00:00',
                'note' => '前月勤務',
            ]);

            // 翌月
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => Carbon::now()->addMonth()->format('Y-m-d'),
                'clock_in' => '08:30:00',
                'clock_out' => '17:30:00',
                'note' => '翌月勤務',
            ]);
        }
    }

    public function test_管理者は全一般ユーザーの名前とメールアドレスを確認できる()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/staff/list');

        $response->assertStatus(200);
        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_管理者は選択したユーザーの勤怠情報を確認できる()
    {
        $user = $this->users->first();
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', Carbon::now()->format('Y-m-d'))
                                ->first();

        $this->assertNotNull($attendance);

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('通常勤務');
    }

    public function test_勤怠一覧画面で前月の情報が表示される()
    {
        $user = $this->users->first();

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/staff/{$user->id}?month=" . Carbon::now()->subMonth()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->subMonth()->format('Y年n月'));
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('前月勤務');
    }

    public function test_勤怠一覧画面で翌月の情報が表示される()
    {
        $user = $this->users->first();

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/staff/{$user->id}?month=" . Carbon::now()->addMonth()->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->addMonth()->format('Y年n月'));
        $response->assertSee('08:30');
        $response->assertSee('17:30');
        $response->assertSee('翌月勤務');
    }

    public function test_管理者は勤怠一覧から勤怠詳細画面に遷移できる()
    {
        $user = $this->users->first();
        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', Carbon::now()->format('Y-m-d'))
                                ->first();

        $this->assertNotNull($attendance);

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
