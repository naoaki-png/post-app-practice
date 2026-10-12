# CLAUDE.md

このファイルは、このリポジトリのコードを扱う際に Claude Code (claude.ai/code) へ向けたガイダンスを提供します。

## 概要

Laravel 10 製の小さな投稿アプリ(投稿の一覧・編集・削除、Fortify による認証付き)。日本語のチュートリアルシリーズ(Tutorial 13〜15:設計 → 実装 → 自動化)の題材として使っています。README とドキュメントは日本語で書かれています。実行環境は Laravel Sail(Docker)+ MySQL なので、コマンドは `./vendor/bin/sail` 経由で実行してください。

このコードは受講生本人が読む・写経することを前提としているため、教材としての読みやすさを優先してください。

## コマンド

```bash
./vendor/bin/sail up -d                      # コンテナを起動
./vendor/bin/sail artisan migrate --seed     # テーブル作成 + シード(ユーザー2、カテゴリ3、投稿4)
./vendor/bin/sail artisan test               # 全テストを実行(PHPUnit)
./vendor/bin/sail artisan test --filter=Name # 単一のテストクラス/メソッドを実行
./vendor/bin/sail down
```

シード済みのログイン情報:`usera@example.com` / `userb@example.com`、パスワードは `password`。アプリは `http://localhost` で表示されます。

## アーキテクチャ

- `routes/web.php`:`/` はウェルカムページ。`/posts`(一覧)、`/posts/{post}/edit`、`PUT/DELETE /posts/{post}` は `auth` ミドルウェアの内側にあります。作成・詳細表示のルートはありません。
- `PostController` は `$this->authorize('update'|'delete', $post)` で所有者チェックを行い、その実体は `app/Policies/PostPolicy.php` です。投稿者以外が URL を直接開くと 403 になり、ビュー側でも他人の投稿の編集・削除ボタンは非表示にしています。権限を変更するときは両方を揃えてください。
- バリデーションは `PostController::update` 内にインラインで書かれています(`title`、`content`、`category_id`)。FormRequest は使っていません。
- 認証は Laravel Fortify(`app/Actions/Fortify`、`FortifyServiceProvider`)。ログイン・登録のビューは `resources/views/auth` にあります。
- モデル:`User`、`Post`(ユーザーとカテゴリに属する)、`Category`。

## ドキュメント用ディレクトリ(アプリのコードではない)

- `docs/` は学習者自身の設計書を置く場所です(draw.io の図、チュートリアル章ごとの画面・API・入力チェック仕様など。例:`docs/13-4/post-api.md`)。`post-*` ファイルはこのアプリについてのもので、`cafe-*` ファイルは別の練習題材(カフェのモバイルオーダーアプリ)であり、ここには実装されていません。
- `answers/` には Tutorial 13 の模範解答があり、アプリには影響しません。コードの正としては扱わないでください。設計書とコードは互いに整合を保つこと(例:API 仕様書と `routes/web.php`)。
- `*:Zone.Identifier` ファイルは Windows のダウンロード時に付く副産物です。無視してください。
