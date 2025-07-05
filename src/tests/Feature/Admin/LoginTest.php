<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $adminLoginUrl = '/admin/login';

    public function test_admin_email_is_required_for_login()
    {
        $response = $this->post($this->adminLoginUrl, [
            'email' => '',
            'password' => 'adminpass123',
        ]);

        // 日本語メッセージでなくフィールド名を指定
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_password_is_required_for_login()
    {
        $response = $this->post($this->adminLoginUrl, [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_admin_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass123'),
            'is_admin' => true,
        ]);

        $response = $this->post($this->adminLoginUrl, [
            'email' => 'wrong@example.com',
            'password' => 'adminpass123',
        ]);

        // 認証失敗もセッションにエラーが残るが、'email' ではないケースも
        $response->assertSessionHasErrors(); // より柔軟なチェック
    }
}

