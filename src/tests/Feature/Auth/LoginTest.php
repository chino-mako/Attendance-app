<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $loginUrl = '/login';

    protected function setUp(): void
    {
        parent::setUp();

        // 共通ユーザーを作成（必要に応じて使い分けてください）
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_email_is_required_for_login()
    {
        $response = $this->post($this->loginUrl, [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function test_password_is_required_for_login()
    {
        $response = $this->post($this->loginUrl, [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post($this->loginUrl, [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
