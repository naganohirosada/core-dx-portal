<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    company: Object
});
</script>

<template>
    <Head :title="company.name + ' - 顧客詳細'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-slate-400 uppercase">Customer File</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-0.5">{{ company.name }} の詳細カルテ</h2>
                </div>
                <Link :href="route('companies.index')" class="text-sm text-slate-500 hover:text-slate-800">← 顧客一覧に戻る</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-1 h-fit">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 mb-4">企業基本情報</h3>
                    <div class="space-y-4 text-sm">
                        <div>
                            <label class="block text-xs font-medium text-slate-400">担当者名</label>
                            <p class="font-semibold text-slate-800 mt-0.5">{{ company.contact_name || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400">メールアドレス</label>
                            <p class="font-mono text-slate-800 mt-0.5">{{ company.email || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400">電話番号</label>
                            <p class="font-mono text-slate-800 mt-0.5">{{ company.phone || '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400">所在地</label>
                            <p class="text-slate-800 mt-0.5">〒{{ company.postal_code }}<br>{{ company.address }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-2">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">プロジェクト発注履歴（紐づく案件一覧）</h3>
                    <div class="space-y-4">
                        <div v-for="proj in company.projects" :key="proj.id" class="p-4 border border-slate-100 rounded-xl hover:bg-slate-50/50 transition flex justify-between items-center">
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm">{{ proj.name }}</h4>
                                <p class="text-xs text-slate-400 mt-1">
                                    予算: <span class="font-mono font-bold text-slate-700">￥{{ proj.budget?.toLocaleString() || '0' }}</span>
                                    | 期間: {{ proj.start_date || '未定' }} 〜 {{ proj.end_date || '未定' }}
                                </p>
                            </div>
                            <Link :href="route('projects.show', proj.id)" class="text-xs bg-indigo-50 text-indigo-600 font-bold px-3 py-1.5 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                                案件カルテ ➔
                            </Link>
                        </div>
                        <div v-if="company.projects?.length === 0" class="text-center py-12 text-sm text-slate-400">
                            まだこの顧客に紐づく案件プロジェクトはありません。
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>