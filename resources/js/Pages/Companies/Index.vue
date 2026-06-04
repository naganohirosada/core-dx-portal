<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    companies: Array,
});
</script>

<template>
    <Head title="顧客一覧" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">顧客マスタ一覧</h2>
                <Link :href="route('companies.create')" class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-500 transition">
                    ＋ 新規顧客を追加
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/70 text-slate-500 font-medium">
                            <tr>
                                <th class="px-6 py-4 text-left">企業名</th>
                                <th class="px-6 py-4 text-left">電話番号</th>
                                <th class="px-6 py-4 text-left">住所</th>
                                <th class="px-6 py-4 text-center">稼働案件数</th>
                                <th class="px-6 py-4 text-center">ステータス</th>
                                <th class="px-6 py-4 text-center">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr v-for="company in companies" :key="company.id" class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 font-semibold text-slate-900">{{ company.name }}</td>
                                <td class="px-6 py-4">{{ company.tel || '-' }}</td>
                                <td class="px-6 py-4 text-slate-500 max-w-xs truncate">{{ company.address || '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                        {{ company.projects_count }} 件
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="company.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                        {{ company.status === 'active' ? '有効' : '無効' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <Link :href="route('companies.show', company.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 transition">
                                        詳細
                                    </Link>
                                    <Link :href="route('companies.edit', company.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-900 transition">
                                        編集
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="companies.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400">登録されている顧客データがありません。</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>