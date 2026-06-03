<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    projects: Array
});

// 金額フォーマット関数
const formatCurrency = (value) => {
    if (!value) return '-';
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(value);
};

// ステータスのバッジ色を返す関数
const statusClass = (status) => {
    const classes = {
        lead: 'bg-amber-50 text-amber-700',
        ongoing: 'bg-blue-50 text-blue-700',
        completed: 'bg-emerald-50 text-emerald-700',
        suspended: 'bg-rose-50 text-rose-700',
    };
    return classes[status] || 'bg-slate-50 text-slate-700';
};

const statusLabel = (status) => {
    const labels = { lead: '商談中', ongoing: '進行中', completed: '完了', suspended: '中断' };
    return labels[status] || status;
};
</script>

<template>
    <Head title="案件一覧" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">案件・プロジェクト一覧</h2>
                <Link :href="route('projects.create')" class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-500 transition">
                    ＋ 新規案件を追加
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/70 text-slate-500 font-medium">
                            <tr>
                                <th class="px-6 py-4 text-left">案件名</th>
                                <th class="px-6 py-4 text-left">顧客名</th>
                                <th class="px-6 py-4 text-left">期間</th>
                                <th class="px-6 py-4 text-right">予算</th>
                                <th class="px-6 py-4 text-center">ステータス</th>
                                <th class="px-6 py-4 text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr v-for="project in projects" :key="project.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ project.name }}</td>
                                <td class="px-6 py-4 text-slate-700 font-medium">{{ project.company ? project.company.name : '未紐付け' }}</td>
                                <td class="px-6 py-4 text-xs text-slate-500">
                                    {{ project.start_date || '未定' }} 〜 {{ project.end_date || '未定' }}
                                </td>
                                <td class="px-6 py-4 text-right font-mono">{{ formatCurrency(project.budget) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="statusClass(project.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ statusLabel(project.status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('projects.edit', project.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 transition">
                                        編集
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="projects.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">登録されている案件データがありません。</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>