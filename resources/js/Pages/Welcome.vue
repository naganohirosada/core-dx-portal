<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="次世代AI型 統合DXポータル" />

    <div class="bg-slate-50 text-slate-900 min-h-screen font-sans selection:bg-indigo-600 selection:text-white">
        <!-- 💡 ナビゲーションヘッダー -->
        <header class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-slate-100 px-6 py-4 flex justify-between items-center max-w-7xl mx-auto rounded-b-2xl shadow-xs">
            <div class="flex items-center space-x-2">
                <span class="text-2xl">🏢</span>
                <div>
                    <span class="text-xl font-black tracking-widest text-slate-900">Core DX Portal</span>
                    <span class="ml-2 text-[10px] bg-indigo-50 text-indigo-600 font-bold px-1.5 py-0.5 rounded">SaaS / AI</span>
                </div>
            </div>

            <nav v-if="canLogin" class="flex items-center space-x-4">
                <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="bg-slate-900 text-white text-sm font-medium px-4 py-2 rounded-xl hover:bg-slate-800 transition">
                    ダッシュボードを開く
                </Link>
                <template v-else>
                    <Link :href="route('login')" class="text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
                        ログイン
                    </Link>
                </template>
            </nav>
        </header>

        <!-- 🚀 ヒーローセクション -->
        <main class="max-w-7xl mx-auto px-6 pt-16 pb-24 text-center space-y-8">
            <h1 class="text-4xl md:text-6xl font-black tracking-tight text-slate-900 max-w-4xl mx-auto leading-tight">
                バックオフィスを、<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">たった1つのポータル</span>で劇的に変える。
            </h1>
            
            <p class="text-base md:text-lg text-slate-500 max-w-2xl mx-auto leading-relaxed">
                勤怠打刻、タスク管理、電子決裁ワークフロー、そしてインボイス対応の請求書発行まで。経営に必要なコア業務を一元化し、AIが生産性を加速させる統合型DXプラットフォーム。
            </p>

            <!-- 📊 機能マッピンググリッド（補助金のプロセス審査へのアピール） -->
            <section class="pt-20 space-y-6">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Core Ecosystem</h2>
                <p class="text-2xl font-bold text-slate-800">業務の壁をなくす 4つのコアシステム</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 pt-6 text-left">
                    <!-- 1. 勤怠・労務 -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs hover:shadow-md transition space-y-3">
                        <div class="text-3xl">🛫</div>
                        <h3 class="font-bold text-slate-900 text-base">リアルタイム勤怠・労務</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">ワンクリックの確実な打刻システム。有給休暇の申請から承認、消化状況までをダッシュボードで一元管理。</p>
                    </div>

                    <!-- 2. 案件・タスク -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs hover:shadow-md transition space-y-3">
                        <div class="text-3xl">📋</div>
                        <h3 class="font-bold text-slate-900 text-base">カンバン式タスク管理</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">直感的なドラッグ＆ドロップ操作のプロジェクトボード。工数実績の記録機能により、案件ごとの収益性を可視化。</p>
                    </div>

                    <!-- 3. ワークフロー -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-xs hover:shadow-md transition space-y-3">
                        <div class="text-3xl">📝</div>
                        <h3 class="font-bold text-slate-900 text-base">稟議・電子決裁</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">経費精算や購買稟議をペーパーレス化。承認ルートの自動化により、社内の意思決定スピードを最大化。</p>
                    </div>

                    <!-- 4. 請求・インボイス -->
                    <div class="bg-white p-6 rounded-2xl border border-indigo-100 bg-indigo-50/5 shadow-xs hover:shadow-md transition space-y-3 relative overflow-hidden">
                        <div class="text-3xl">💴</div>
                        <h3 class="font-bold text-indigo-900 text-base">インボイス対応請求管理</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">適格請求書（T番号）の出力、税率ごとの自動税額計算・端数処理を標準装備。売上ステータスもスマートに追跡。</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-slate-200/60 py-8 text-center text-xs text-slate-400">
            &copy; 2026 Core DX Portal. All Rights Reserved.
        </footer>
    </div>
</template>