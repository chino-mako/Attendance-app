<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_datetime_is_displayed_on_attendance_page()
    {
        // 日時を固定（テストの安定性向上）
        Carbon::setTestNow(Carbon::create(2025, 7, 4, 10, 30)); // 2025年7月4日 10:30

        // ログインユーザー作成
        $user = User::factory()->create();

        // ログイン状態でアクセス
        $response = $this->actingAs($user)->get('/attendance');

        // 表示されているべき日付フォーマット（Bladeでこれを表示している前提）
        $expectedDateTime = Carbon::now()->format('Y年m月d日 H:i');

        // 確認
        $response->assertStatus(200);
        $response->assertSee($expectedDateTime);
    }
}
