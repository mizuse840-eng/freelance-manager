#!/bin/sh
set -e

APP_DIR=/var/www/html/fuel/app

# app/ はホストからのバインドマウントのため、fresh clone 直後は
# tmp/logs/cache が存在しないことがある。FuelPHPが実行時に生成する
# ファイル（config/crypt.php, セッション, キャッシュ, ログ）を置くため
# ディレクトリを作成し、www-data から書き込み可能にしておく。
mkdir -p "$APP_DIR/tmp/sessions" "$APP_DIR/tmp/cache" "$APP_DIR/logs" "$APP_DIR/cache"

chmod 777 "$APP_DIR/config"
chmod -R 777 "$APP_DIR/tmp" "$APP_DIR/logs" "$APP_DIR/cache"

exec docker-php-entrypoint "$@"
