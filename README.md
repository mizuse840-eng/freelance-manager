# フリーランス案件管理アプリ

フリーランスとして受注した案件とタスクを一元管理するWebアプリです。Lancers経由での受注経験をもとに、案件ごとの期限とステータスを把握しづらいという課題から企画しました。

クライアント・案件・タスクを階層で管理し、期限までの残り日数を色分けして表示します。

## 使用技術

| 分類 | 技術 |
|---|---|
| フレームワーク | FuelPHP 1.8.2 |
| 言語 | PHP 8.1 |
| データベース | MySQL 8.0 |
| フロントエンド | Knockout.js / Bootstrap 5.3 |
| 実行環境 | Docker（php:8.1-apache） |

## 機能一覧

### 認証
- ログイン / ログアウト
- 未ログイン時は全画面からログイン画面へリダイレクト

### クライアント管理
- 一覧表示
- 新規登録
- 一覧上でのインライン編集（非同期・画面遷移なし）
- 削除（案件が紐づいている場合は削除不可）

### 案件管理
- クライアントごとの一覧表示
- 新規登録 / 編集 / 削除（タスクが紐づいている場合は削除不可）
- 案件URLの登録（任意項目）。登録すると一覧の案件名の横にリンクを表示
- 期限までの残り日数を色分け表示（超過・3日以内・7日以内・それ以外）
- ステータスの非同期更新（ドロップダウンでページ遷移なしに反映）

### タスク管理
- 案件ごとの一覧表示
- 新規登録 / 編集 / 削除
- メモの登録（改行を保持して表示）
- 期限までの残り日数を色分け表示
- ステータスの非同期更新

## データ構造

`users` → `clients` → `projects` → `tasks` の4階層です。

```
users
 └── clients          ユーザーが登録したクライアント
      └── projects    クライアントから受注した案件
           └── tasks  案件を構成するタスク
```

ステータスはマスタテーブルで管理しています。

- `project_statuses` … 案件のステータス（未着手 / 進行中 / 完了）
- `task_statuses` … タスクのステータス（未着手 / 進行中 / 完了）

`projects.project_status_id` と `tasks.task_status_id` から参照します。表示順は `sort_order` で制御しています。

テーブル定義は [`schema.sql`](schema.sql) を参照してください。FuelPHPが自動生成する `migration` テーブルは、マイグレーションの適用状況を記録するフレームワークの管理用テーブルでアプリのデータ構造ではないため除外しています。ステータスマスタは初期データが無いと選択肢が空になり登録できないため、INSERT文を含めています。

## 環境構築

### 必要なもの

- Docker Desktop

### 手順

```bash
git clone https://github.com/mizuse840-eng/freelance-manager.git
cd freelance-manager
docker compose -p freelance up -d
```

**`-p freelance` を付けてください。** Docker Composeはプロジェクト名を省略するとカレントディレクトリ名から自動生成しますが、その際に英数字以外の文字は除去されます。開発環境のディレクトリ名が「フリーランス案件管理」で日本語のみだったため、除去後に文字が残らずプロジェクト名が空になり、`project name must not be empty` で起動に失敗しました。

上記のとおり `freelance-manager` にcloneした場合はディレクトリ名が英数字なので省略しても起動しますが、その場合はコンテナ名やボリューム名が変わるため、このREADME内の他のコマンドも全て `-p` を外す必要があります。混乱を避けるため `-p freelance` に統一しています。

### データベースの接続情報

接続情報はソースコードに持たせず、環境変数から読み込んでいます（[`app/fuel/app/config/db.php`](app/fuel/app/config/db.php)）。値は `docker-compose.yml` で定義しています。

| 環境変数 | 既定値 |
|---|---|
| `DB_HOST` | `mysql` |
| `DB_NAME` | `freelance_db` |
| `DB_USER` | `user` |
| `DB_PASSWORD` | `password` |
| `DB_ROOT_PASSWORD` | `root` |

`${VAR:-default}` の形でローカル開発用の既定値を持たせているため、上記の手順のまま起動できます。別の環境で動かす場合は `.env` かシェルの環境変数で上書きします。

```bash
DB_PASSWORD=xxxxx docker compose -p freelance up -d
```

環境の違いは環境変数で表現するため、`config/development/db.php` のような環境別の設定ファイルは置いていません。FuelPHPの雛形にあった `production` / `staging` / `test` の `db.php` は、接続先もパスワードも雛形の値のままで使われていなかったため削除しました。

### マイグレーション

コンテナ起動後、テーブルの作成と初期データの投入を行います。

```bash
docker compose -p freelance exec php php oil refine migrate
```

作成されるもの:

| マイグレーション | 内容 |
|---|---|
| `001`〜`006` | 6テーブルの作成 |
| `007` | ステータスマスタの初期データ |
| `008` | 動作確認用ユーザー |
| `009` | `projects` への `url` カラム追加 |

マイグレーションの適用状況は `fuel/app/config/development/migrations.php` に記録されます。このファイルは `.gitignore` の対象です。DBが空なのに「適用済み」と記録されている場合は、ファイルを削除してから再実行してください。

```bash
rm app/fuel/app/config/development/migrations.php
docker compose -p freelance exec php php oil refine migrate
```

### アクセス

http://localhost:8080

| 項目 | 値 |
|---|---|
| メールアドレス | `test@example.com` |
| パスワード | `password` |

この動作確認用ユーザーはマイグレーション `008_seed_user.php` で作成されます。

### ログの確認

ログはDockerボリューム上にあるため、ホスト側の `app/fuel/app/logs/` からは参照できません。

```bash
docker compose -p freelance exec php tail -f fuel/app/logs/$(date +%Y/%m/%d).php
```

バインドマウントに置いていた際、書き込みが断続的に失敗して全エンドポイントが500を返す問題が発生したため、ボリュームに移しました。

## セキュリティ対策

### CSRF

全てのPOST処理で `Security::check_token()` によるトークン検証を行っています。フォームには `Form::csrf()` でトークンを埋め込み、検証に失敗した場合は404ではなくエラーメッセージを表示して画面を再表示します。再表示時に新しいトークンが埋め込まれるため、利用者はそのまま送信し直せます。

ログイン処理も対象です。ログインCSRFは、攻撃者のアカウントに強制的にログインさせてその後の入力を攻撃者のアカウントに記録させる攻撃が成立するため、認証処理より前（DBを参照する前）に検証しています。

非同期更新APIでは、CSRFトークンがリクエストのたびに再生成される仕様に対応するため、レスポンスに最新のトークンを含めてJavaScript側で差し替えています（`Controller_Base::json_response()`）。これを行わないと2回目以降の非同期更新が必ず失敗します。

#### フレームワーク標準の設定との比較

FuelPHPはCSRF対策を自動化する設定を持っています。本アプリがどれを使い、どれを使っていないかを整理します。いずれも `fuel/core/config/config.php` の既定値のままです。

| 設定 | 既定値 | 本アプリ | 使用しない理由 |
|---|---|---|---|
| `csrf_autoload` | `false` | 使わない | POST/PUT/DELETEを自動検証する設定。有効にすると全POSTが一律で弾かれ、画面ごとにエラーの出し方を変えられない |
| `csrf_bad_request_on_fail` | `false` | 使わない | 検証失敗時に400を返す設定。利用者には素の400ではなく「セッションの有効期限が切れました」と表示し、そのまま再送信できる導線を出したい |
| `csrf_auto_token` | `false` | 使わない | 出力フィルタでフォームにhiddenを自動挿入する設定。非同期更新ではJavaScript側でもトークンを扱う必要があり、結局 `Security::fetch_token()` を明示的に呼ぶことになるため、フォーム側だけ自動化しても一貫しない |
| `csrf_rotate` | `true` | 既定のまま使用 | 検証成功後にトークンを再生成する。使い回しを防げるため有効のまま |
| `csrf_expiration` | `0` | `0` を明示 | 後述 |

自動化を使わず `Form::csrf()` と `Security::check_token()` を明示的に書いているのは、**検証失敗時のUXを画面ごとに制御するため**です。コード量は増えますが、利用者が入力内容を失わずに再送信できます。

#### `csrf_expiration => 0` について

この値はトークンの有効期限ではなく、**トークンを保存するcookieの寿命（秒）**です。`0` は無期限ではなく、**ブラウザを閉じると消えるセッションcookie**を意味します。

```php
// core/classes/security.php … トークンはcookieに保存される
\Cookie::set(static::$csrf_token_key, static::$csrf_token, $expiration);

// core/classes/cookie.php … 0 はそのまま setcookie に渡る
$expiration = $expiration > 0 ? $expiration + time() : 0;
return setcookie($name, $value, $expiration, ...);
```

`setcookie()` の第3引数が `0` の場合はセッションcookieになるため、正の秒数を設定するより寿命は短くなります。

またトークン自体は、`Security::fetch_token()` がリクエスト内の初回呼び出しで必ず `set_token(true)` を通るため、**画面を描画するたびに再生成されています**。`csrf_expiration` は「同じトークンを使い回せる期間」ではありません。

以上から、現在の設定はトークンを長生きさせるものではないため、値の変更は行っていません。

### XSS

出力コンテキストごとにエスケープ方法を分けています。

- HTMLとして出力する箇所は `e()`（`Security::htmlentities`）でエスケープ
- 一覧画面のように `<script>` 内へJSONで埋め込む箇所は、Viewの自動エスケープを無効化（`View::forge(..., false)`）した上で、`json_encode()` に `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT` を指定してJavaScriptコンテキスト向けにエスケープ

タスクのメモは改行を `<br>` に変換して表示する必要があるため、エスケープしてから `nl2br()` を適用しています。

### SQLインジェクション

DBアクセスは全てModelクラスに集約し、FuelPHPのクエリビルダ（`DB::select()` / `DB::insert()` / `DB::update()` / `DB::delete()`）を使用しています。`DB::query()` による文字列組み立ては使用していません。

### アクセス制御

Modelの検索メソッドは全て `user_id` による絞り込みを含みます。`projects` と `tasks` は `user_id` を直接持たないため、`clients` とJOINして絞り込んでいます。

```php
// Model_Project::find_by_id() の例
->from('projects')
->join('clients')->on('projects.client_id', '=', 'clients.id')
->where('projects.id', $id)
->where('clients.user_id', $user_id)
```

これにより、他のユーザーのIDをURLに指定してもレコードが取得できず404になります。更新・削除も、更新前に所有確認を行ってから実行しています。

### セキュリティヘッダ

`Trait_Headers` で以下を全画面に付与しています。

| ヘッダ | 値 | 目的 |
|---|---|---|
| `X-Frame-Options` | `SAMEORIGIN` | クリックジャッキング対策 |
| `X-Content-Type-Options` | `nosniff` | MIMEタイプの推測を禁止 |
| `Referrer-Policy` | `same-origin` | 外部サイトへの遷移時にURLを送らない |

またPHPのバージョン情報を隠すため、Dockerfileで `expose_php = Off` を設定しています。

### パスワード

`password_hash()`（bcrypt）でハッシュ化し、`password_verify()` で照合しています。平文での保存・ログ出力は行っていません。

## 設計判断

レビューでのご指摘に対し、意図的に対応を見送った箇所と、一部のみ対応した箇所があります。コードを見ただけでは意図が伝わらないため記載します。

### 1. FuelPHPのAuthクラスを採用しなかった

「ログイン認証にFuelPHPのAuthクラスを使うことを検討する」というご指摘について、SimpleAuthの実装を確認した上で**現状の独自実装を維持する判断**をしました。

**理由1: bcryptと原理的に併用できない**

SimpleAuthの `validate_user()` は、入力されたパスワードを先にハッシュ化してからSQLの等値比較で照合します。

```php
// fuel/packages/auth/classes/auth/login/simpleauth.php
$password = $this->hash_password($password);
$this->user = \DB::select_array(...)
    ->where('username', '=', $username_or_email)
    ->where('password', '=', $password)   // SQLでの等値比較
```

bcryptはハッシュごとにソルトが異なるため、DBの行を取得する前にハッシュ値を計算できません。この構造では照合が成立しません。`hash_password()` だけを差し替えても解決せず、`validate_user()` ごとオーバーライドした独自ドライバが必要になります。

**理由2: パスワードハッシュの強度が下がる**

```php
// 現状: bcrypt（ユーザーごとに異なるソルト、コスト調整可）
password_hash($password, PASSWORD_DEFAULT)

// SimpleAuth: PBKDF2 + 全ユーザー共通の固定ソルト
base64_encode(hash_pbkdf2('sha256', $password, \Config::get('auth.salt'), 10000, 32, true))
```

SimpleAuthは全ユーザーで同一のソルトを使うため、レインボーテーブルへの耐性がbcryptより劣ります。課題条件に「セキュリティ資料を読み必要な実装を行う」とあるため、強度を下げる変更は方針に反すると判断しました。

**あわせて必要になる変更**

SimpleAuthは `username` / `group` / `last_login` / `login_hash` / `profile_fields` の5カラムを要求するため、`users` テーブルのマイグレーションも必要になります。

**相談したい点**

Authクラスを採用することで `login_hash` によるセッションハイジャック対策が得られる点は利点だと考えています。一方でパスワードハッシュの強度は下がります。この判断が妥当か、あるいは `Auth_Login_Driver` を継承して `validate_user()` と `hash_password()` をオーバーライドし、bcryptを維持したままAuthのインターフェースに載せる折衷案を採るべきか、ご意見をいただけると助かります。

### 2. observableArray を残した

「observableしている変数が本当に監視が必要か確認する」というご指摘について、一部のみ対応しました。

**削除したもの: `status_name`**

案件一覧・タスク一覧の `status_name` は、どのバインディングからも参照されていない死にコードでした。ステータス名は `$root.statuses` の `optionsText` から描画しており、行ごとに保持する必要がありません。初期JSON・observable宣言・API応答の代入の3箇所すべてを削除しました。

**残したもの: `clients` / `projects` / `tasks` の `observableArray`**

これらは行の増減が発生しないため、現時点では素の配列でも動作します。ただしKnockoutの `foreach` バインディングは素の配列だと変更検知が働かず、配列を書き換えても再描画されません。

将来インラインでの行追加や非同期削除を実装した際に、「配列は更新されているのに画面が変わらない」という原因を特定しづらい不具合になります。コレクションを `foreach` に渡す場合は `observableArray` が正しい使い方だと考え、残しています。

こちらもご意見をいただけると助かります。

### 3. Modelの共通化は定型部分のみに留めた

「4モデルで `find_by_id` / `create` / `update` / `delete` の構造がほぼ同型」というご指摘を受け、`Model_Base` を追加しました。ただし全てを共通化はしていません。

**共通化したもの**

`create` / `update` / `delete` の実体である「タイムスタンプを付けてINSERT / UPDATE / DELETEする」部分です。INSERT / UPDATE / DELETE の記述は3モデル合計9箇所から `Model_Base` の3箇所になり、`created_at` / `updated_at` の付与漏れも構造的に起きなくなりました。

**共通化しなかったもの**

検索系（`find_by_id` など）は各モデルに残しました。`Model_Client` は `clients` を単体で引くだけですが、`Model_Project` / `Model_Task` は所有者を判定するために2〜3テーブルをJOINしており、JOINの経路も取得カラムも異なります。まとめても引数で分岐するだけのメソッドになり、かえって読みにくくなると判断しました。

所有確認の方式も揃えていません。`clients` は `user_id` を直接持つのでWHERE句に足すだけで済みますが、`projects` / `tasks` は持たないため事前に `find_by_id` で確認する必要があります。無理に揃えると `clients` 側に不要なクエリが1回増えます。両方に対応できるよう、`update_by_id` と `delete_by_id` は追加のWHERE条件を受け取れるようにしました。

なお `Model_User` は `Model_Base` を継承していません。ユーザー登録機能が無く `create` / `update` / `delete` を持たないため、共通化する対象がありません。

## ディレクトリ構成

```
app/fuel/app/
├── classes/
│   ├── controller/
│   │   ├── base.php      認証チェック・JSONレスポンスの共通処理
│   │   ├── auth.php      ログイン / ログアウト
│   │   ├── client.php    クライアント管理
│   │   ├── project.php   案件管理
│   │   ├── task.php      タスク管理
│   │   └── welcome.php   404ページ
│   ├── model/
│   │   ├── base.php      Modelの共通処理（タイムスタンプ付与とCRUD）
│   │   ├── user.php
│   │   ├── client.php
│   │   ├── project.php
│   │   └── task.php
│   ├── deadline.php      期限の残り日数と表示色の計算
│   └── trait/headers.php セキュリティヘッダ
├── config/
│   └── freelance.php     期限の警告日数と表示色の設定
├── migrations/
└── views/
```

### 実装方針

- **POSTの処理は `post_XXX` に分離** … `Input::method() === 'POST'` による分岐は使わず、FuelPHPの `get_XXX` / `post_XXX` によるディスパッチを使用しています
- **DBアクセスはModelに集約** … Controllerに直接クエリを書いていません
- **データの整形はController** … 残り日数の計算やメモのHTML化はControllerで行い、Viewは受け取った値を表示するだけにしています。ただし `json_encode()` のフラグ指定は「`<script>` 内に埋め込む」という出力形式の都合であるためViewに残しています
- **バリデーションは早期リターン** … `elseif` の連鎖ではなく、最初に見つかったエラーを返す形にしています
- **期限の判定ルールは設定に切り出し** … 警告日数と表示色は `config/freelance.php` で管理しています
