<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private string $registerUrl = '/register';

    public function test_name_is_required()
    {
        $response = $this->post($this->registerUrl, [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $response->assertSeeText('お名前を入力してください');
    }

    public function test_email_is_required()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSeeText('メールアドレスを入力してください');
    }

    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSeeText('パスワードは8文字以上で入力してください');
    }

    public function test_password_confirmation_must_match()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSeeText('パスワードと一致しません');
    }

    public function test_password_is_required()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $response->assertSeeText('パスワードを入力してください');
    }

    public function test_user_can_register_with_valid_data()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'テスト太郎',
        ]);
    }
}
