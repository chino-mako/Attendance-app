<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StatusTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithStatus(string $status): User
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::today(),
            'status'    => $status,
        ]);
        return $user;
    }

    public function test_status_is_displayed_as_k勤務外_for_user()
    {
        $user = $this->createUserWithStatus('勤務外');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_displayed_as_k出勤中_for_user()
    {
        $user = $this->createUserWithStatus('出勤中');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_displayed_as_k休憩中_for_user()
    {
        $user = $this->createUserWithStatus('休憩中');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_displayed_as_k退勤済_for_user()
    {
        $user = $this->createUserWithStatus('退勤済');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
