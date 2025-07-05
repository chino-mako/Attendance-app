# 環境構築
## Dockerビルド
- git clone https://github.com/chino-mako/Attendance-app
- docker-compose up -d --build

## Laravel 環境構築
- docker-compose exec php bash
- composer install
- .env.example をコピーして.env ファイルを作成し、環境変数を変更
- php artisan key:generate
- php artisan migrate
- php artisan db:seed
- php artisan storage:link

# 使用技術
- PHP:7.3/8.0
- Laravel:8.75
- MySQL:8.0.26
- mailhog

# URL
- 開発環境: http://localhost/
- phpMyAdmin: http://localhost:8080/

# ER図

![index](https://github.com/user-attachments/assets/b22ce2ca-a32f-4bd3-be36-be3fb31b8f10)
