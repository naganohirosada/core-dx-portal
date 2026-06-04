<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

// スマホ用サイドバーの開閉状態
const isMobileMenuOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// 💡 ログインユーザーの情報を使いやすいように取得
const user = usePage().props.auth.user;

// 💡 権限（adminまたはmanager）を持っているか判定する関数（Vue側での表示制御用）
const canManage = () => {
    return ['admin', 'manager'].includes(user.role);
};
</script>

<template>
    <div class="min-h-screen bg-slate-100/60 flex flex-col md:flex-row font-sans">
        
        <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center shadow-md z-40">
            <span class="font-extrabold tracking-wider text-sm">Coredesia SaaS</span>
            <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="p-2 focus:outline-none hover:bg-slate-800 rounded-lg">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <aside :class="isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'" class="fixed md:sticky top-0 left-0 bottom-0 w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-5 z-50 transform transition-transform duration-300 ease-in-out md:shadow-none shadow-2xl h-screen">
            
            <div class="space-y-6">
                <div class="flex items-center justify-between px-2 py-3 border-b border-slate-800">
                    <div>
                        <h1 class="text-white font-black tracking-widest text-lg">Coredesia</h1>
                        <span class="text-[10px] text-indigo-400 font-bold uppercase tracking-widest">Enterprise Ver</span>
                    </div>
                    <button @click="isMobileMenuOpen = false" class="md:hidden text-slate-400 hover:text-white">✕</button>
                </div>

                <nav class="space-y-1">
                    <Link :href="route('dashboard')" :class="route().current('dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>📊</span> <span>総合ダッシュボード</span>
                    </Link>
                    <Link :href="route('companies.index')" :class="route().current('companies.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>🏢</span> <span>顧客マスタ管理</span>
                    </Link>
                    <Link :href="route('projects.index')" :class="route().current('projects.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>📁</span> <span>案件・タスク管理</span>
                    </Link>
                    <Link :href="route('events.index')" :class="route().current('events.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>🗓️</span> <span>共有カレンダー</span>
                    </Link>
                    <Link :href="route('workflows.index')" :class="route().current('workflows.index') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>📝</span> <span>ワークフロー申請</span>
                    </Link>
                    <Link :href="route('invoices.index')" :class="route().current('invoices.*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white'" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition">
                        <span>💴</span> <span>請求書・売上管理</span>
                    </Link>
                </nav>
            </div>

            <div class="border-t border-slate-800 pt-4 space-y-3">
                <div class="px-2">
                    <p class="text-xs text-slate-500">ログインユーザー</p>
                    <p class="text-sm font-bold text-white mt-0.5 truncate">{{ user.name }}</p>
                    <span :class="canManage() ? 'bg-indigo-500/20 text-indigo-400' : 'bg-slate-800 text-slate-400'" class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-md mt-1">
                        🔑 {{ user.role === 'admin' ? '管理者' : user.role === 'manager' ? 'マネージャー' : '一般社員' }}
                    </span>
                </div>
                <button @click="logout" class="w-full text-left flex items-center space-x-3 px-3 py-2 rounded-lg text-xs font-semibold text-rose-400 hover:bg-rose-500/10 transition">
                    <span>🚪</span> <span>システムからログアウト</span>
                </button>
            </div>
        </aside>

        <div v-if="isMobileMenuOpen" @click="isMobileMenuOpen = false" class="fixed inset-0 bg-black/40 z-40 md:hidden backdrop-blur-xs"></div>

        <main class="flex-1 flex flex-col overflow-x-hidden min-h-screen">
            <header v-if="$slots.header" class="bg-white border-b border-slate-100 shadow-xs px-6 py-5 z-20">
                <div class="mx-auto max-w-7xl">
                    <slot name="header" />
                </div>
            </header>

            <div class="flex-1">
                <slot />
            </div>
        </main>
    </div>
</template>