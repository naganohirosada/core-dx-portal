<!-- resources/js/Pages/Accounting/Receivables.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    buckets: Object,
    summary: Array,
    totals: Object,
    rows: Array,
    today: String,
});

const formatNumber = (num) => Number(num).toLocaleString();
</script>

<template>
    <Head title="売掛金 年齢調べ" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">債権管理</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-1">売掛金 年齢調べ</h2>
                </div>
                <p class="text-xs text-slate-500">基準日: {{ today }}</p>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- 顧客別 × 経過区分 -->
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <h3 class="text-base font-bold text-slate-900 mb-4">顧客別 未回収残高（支払期日からの経過日数）</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-100 text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-medium">
                                    <th class="border border-slate-100 px-4 py-3 text-left">顧客</th>
                                    <th v-for="(label, key) in buckets" :key="key" class="border border-slate-100 px-4 py-3 text-right">{{ label }}</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right">合計</th>
                                </tr>
                            </thead>
                            <tbody class="text-slate-700">
                                <tr v-for="line in summary" :key="line.company_name">
                                    <td class="border border-slate-100 px-4 py-3 font-semibold">{{ line.company_name }}</td>
                                    <td v-for="(label, key) in buckets" :key="key" class="border border-slate-100 px-4 py-3 text-right font-mono" :class="key !== 'current' && line[key] > 0 ? 'text-rose-600 font-bold' : ''">
                                        {{ line[key] ? '¥' + formatNumber(line[key]) : '-' }}
                                    </td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-mono font-bold">¥{{ formatNumber(line.total) }}</td>
                                </tr>
                                <tr v-if="summary.length" class="bg-slate-50 font-bold">
                                    <td class="border border-slate-100 px-4 py-3">合計</td>
                                    <td v-for="(label, key) in buckets" :key="key" class="border border-slate-100 px-4 py-3 text-right font-mono">¥{{ formatNumber(totals[key]) }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-mono">¥{{ formatNumber(totals.total) }}</td>
                                </tr>
                                <tr v-if="summary.length === 0">
                                    <td :colspan="Object.keys(buckets).length + 2" class="text-center py-8 text-slate-400">未回収の売掛金はありません。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 請求書別の明細 -->
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-slate-900">未回収の請求書一覧</h3>
                        <Link :href="route('invoices.index')" class="text-xs text-indigo-600 hover:underline">請求書画面で入金消込 ➔</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-medium">
                                <tr>
                                    <th class="p-3">請求番号</th>
                                    <th class="p-3">顧客</th>
                                    <th class="p-3">支払期日</th>
                                    <th class="p-3 text-right">経過日数</th>
                                    <th class="p-3 text-right">請求額</th>
                                    <th class="p-3 text-right">未回収残高</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                <tr v-for="row in rows" :key="row.id">
                                    <td class="p-3 font-mono text-xs font-bold text-slate-900">{{ row.invoice_number }}</td>
                                    <td class="p-3">{{ row.company_name }}</td>
                                    <td class="p-3 font-mono text-xs">{{ row.due_date }}</td>
                                    <td class="p-3 text-right" :class="row.overdue_days > 0 ? 'text-rose-600 font-bold' : ''">{{ row.overdue_days > 0 ? row.overdue_days + '日超過' : '期日内' }}</td>
                                    <td class="p-3 text-right font-mono">¥{{ formatNumber(row.amount) }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">¥{{ formatNumber(row.outstanding) }}</td>
                                </tr>
                                <tr v-if="rows.length === 0">
                                    <td colspan="6" class="p-6 text-center text-xs text-slate-400">未回収の請求書はありません。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
