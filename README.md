開発環境（Laravel Sail / Docker）の構成を活かしつつ、Renderの無料〜格安枠で「Webサーバー」と「データベース」をパパッと立ち上げる最短ルートです。

---

## 🚀 Renderで最速デモ公開する5ステップ

全体フローは以下の通りです。

```
[ あなたのPC ] ➔ プッシュ ➔ [ GitHub ] ➔ 自動検知 ➔ [ Render (本番サーバー) ]

```

---

### ステップ1：本番用のデータベース（PostgreSQL）を起動

Renderでは、無料/格安で**PostgreSQL**のデータベースをワンクリックで作成できます。Laravelは設定1つでMySQLからPostgreSQLに切り替えられるため、本番はRenderと相性が良いPostgreSQLを使います。

1. [Render公式サイト](https://render.com/)にログインし、画面右上の **「New」➔「PostgreSQL」** を選択。
2. 以下の通り入力して「Create Database」をクリック。
* **Name**: `coredesia-db`
* **Database**: `coredesia`
* **User**: `dbuser`
* **Region**: `Singapore` (日本から一番近いリージョン)


3. 作成後、画面に表示される **「Internal Database URL」**（`postgres://...` から始まる文字列）をメモ帳にコピーしておきます。

---

### ステップ2：本番デプロイ用の「Dockerfile」を配置

Renderに「これはLaravelのシステムだよ」と教えてあげるために、プロジェクトのルートディレクトリ（`core-dx-portal` 直下）に本番専用の軽量な設定ファイルを1枚だけ作成します。

#### ① `Dockerfile` の作成

プロジェクトのルート直下に `Dockerfile` という名前（拡張子なし）でファイルを作成し、以下の内容をそのまま貼り付けます。

```dockerfile
FROM php:8.3-apache

# 必要なLinuxパッケージとPHP拡張モジュールのインストール
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip git nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql gd

# Apacheの設定（ドキュメントルートをpublicに変更）
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# ワークディレクトリの設定
WORKDIR /var/www/html
COPY . .

# Composerのインストールと実行
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# フロントエンド（Vite）のビルド
RUN npm install && npm run build

# 権限の変更
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# ポートの指定
EXPOSE 80

```

---

### ステップ3：GitHubへソースコードをプッシュ

RenderはGitHubのリポジトリと連動して動くため、現在のコードをGitHubにアップロードします。

WSL2のターミナルで以下を順番に実行します。

```bash
git add .
git commit -m "feat: 本番デプロイ用Dockerfileの追加"
git push origin main

```

---

### ステップ4：Renderで「Web Service」を作成

いよいよWebサーバー（画面を映すコンテナ）を立ち上げます。

1. Renderの管理画面右上 **「New」➔「Web Service」** を選択。
2. **「Build and deploy from a Git repository」** を選択し、自分のGitHubから `core-dx-portal` のリポジトリを連携します。
3. 設定画面で以下のように入力します。
* **Name**: `coredesia-portal` (これがURLの一部になります)
* **Region**: `Singapore`
* **Branch**: `main`
* **Language**: `Docker` (★ここをDockerにすることで、ステップ2のファイルが自動起動します)
* **Instance Type**: `Free`（または一番安い有料プラン。Freeプランは15分アクセスがないと一瞬スリープしますが、審査用なら十分です）


4. 最下部の **「Advanced」** ボタンをクリックして、次のステップ5（環境変数）に進みます。

---

### ステップ5：環境変数（`.env`）の投入

「Advanced」エリアにある **「Add Environment Variable」** をクリックして、Laravelを本番モードで動かすための鍵（環境変数）を登録していきます。

以下のセットを1つずつ追加してください。

| KEY | VALUE | 補足 |
| --- | --- | --- |
| `APP_ENV` | `production` | 本番モードで起動

 |
| `APP_DEBUG` | `false` | エラー画面を隠してセキュリティ強化

 |
| `APP_KEY` | `base64:xxxxxxx...` | ローカルの `.env` にある値をそのままコピー

 |
| `DB_CONNECTION` | `pgsql` | PostgreSQLに切り替え

 |
| `DATABASE_URL` | *(ステップ1でメモしたInternal Database URL)* | `postgres://...` のURLをそのままペースト |

入力が終わったら、一番下の **「Create Web Service」** をクリックします！

---

## 🏁 あとは待つだけでURLが発行されます！

ボタンを押すと、Renderが自動的にプログラムのビルド、Viteのコンパイル、サーバーの起動までを全自動で実行してくれます（約5〜10分）。

ログ画面に `Multi-stage build success` や `Live` と表示され、画面左上のURL（`[https://coredesia-portal.onrender.com](https://coredesia-portal.onrender.com)` など）をクリックして「統合DXポータル：Coredesia」の美しいトップページ（LP）が表示されれば完全大成功です！🎉

---

インボイスのシステム改修から、タブUI、日本語化、そして本番インフラの手順まで怒涛のスピードで一気に揃いましたね。

Renderのビルド中、あるいはURLが無事に立ち上がったのち、**「補助金審査のために、データベースにテスト用のA社・B社のデモデータを一発で流し込む設定（Seeder）」** の準備に取り掛かりましょうか？




結論から言うと、**補助金の審査を通す「だけ」なら、実はドメインの取得は必須ではありません！**

Renderが発行してくれる `[https://coredesia-portal.onrender.com](https://coredesia-portal.onrender.com)` のURLのままでも、審査官がブラウザでアクセスして動けば審査自体は通過します。

ですが、ITベンダー登録をして今後実際にお客様（テナント企業）に販売していくことや、事務局の審査官に「ちゃんとした市販の商用SaaSプロダクトである」という**強烈な信頼感（ハク）を与えるためには、独自ドメイン（例：`coredesia.com` など）をサクッと取得して紐付けるのが圧倒的におすすめ**です！

Renderへの独自ドメイン（カスタムドメイン）の紐付けは驚くほど簡単ですので、その手順を3ステップでまとめました。

---

## 🌐 独自ドメイン取得 ➔ Render紐付けの3ステップ

### ステップ1：ドメインの購入

まずは好きなドメインを取得します。2026年現在、世界で最もDNSの反映が早くセキュリティも強い **Cloudflare（クラウドフレア）** や、国内大手の **お名前.com**、**ムームードメイン** などで取得するのがおすすめです。

* **おすすめのドメイン名**: `coredesia.com` や `coredesia.jp`、`coredesia.io` など、製品名がそのまま入っているもの。
* **コスト**: `.com` や `.net` であれば、年間1,500円〜2,500円程度で維持できます。

### ステップ2：Renderの管理画面にドメインを登録

ドメインを手に入れたら、Render側に「このURLが来たら、さっき作ったLaravelのWebサービスに繋いでね」と設定します。

1. Renderの管理画面で、先ほど作った Web Service (`coredesia-portal`) を開きます。
2. 左メニューの **「Settings」** をクリックし、少し下にある **「Custom Domains」** エリアを見つけます。
3. **「Add Custom Domain」** をクリックし、取得したドメイン（例：`[www.coredesia.com](https://www.coredesia.com)` や `coredesia.com`）を入力して保存します。

### ステップ3：DNS設定（ドメインとサーバーを繋ぐ道路工事）

最後に、ドメインを買ったサイト（お名前.comなど）の管理画面（DNS設定環境）を開き、Renderのサーバーを指し示すように以下のレコードを1行だけ追加します。

* **TYPE（種別）**: `CNAME`
* **NAME（ホスト名）**: `www`
* **VALUE（値）**: `coredesia-portal.onrender.com`（Renderが発行した元のURL）

> 💡 **ポイント:**
> これだけで、Renderが裏側で **SSL証明書（HTTPS化のための暗号化）を全自動かつ無料** で発行してくれます。めんどくさいサーバーセキュリティの設定は一切不要です！

---

## 🎯 独自ドメインを付けることのメリット（補助金対策）

審査官は、提出されたURLだけでなく、その企業のコーポレートサイトや製品LPのドメインの一致度なども見ることがあります。
`onrender.com` のままだと「あ、開発中のテスト環境だな」と見抜かれますが、独自ドメインに変わった瞬間に「あ、実在する製品パッケージだ」と一認識されるため、減点リスクを極限まで減らすことができます。

まずは元のRenderのURLで動くところまで確認し、その後にドメインを検討する形でも全く遅くありません！

インフラ周りもいよいよ大詰めですね。**ドメインの取得（お名前.comなどのアカウント準備）はこれから進める形になりそうでしょうか？**