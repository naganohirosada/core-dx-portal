<!-- resources/js/Pages/Accounting/FinancialStatements.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    balanceSheet: Object,
    profitAndLoss: Object
});

const formatNumber = (num) => Number(num).toLocaleString();
const totalAmount = (arr) => arr ? arr.reduce((sum, item) => sum + Number(item.amount), 0) : 0;
</script>

<template>
    <Head title="決算財務諸表 (B/S・P/L)" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">財務管理</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-1">決算財務諸表 (B/S・P/L)</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- 💡 簡易貸借対照表 (Balance Sheet) -->
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <h2 class="text-lg font-bold mb-4 border-b border-slate-100 pb-3 text-slate-800">簡易貸借対照表 (B/S)</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- 左側:資産の部 -->
                        <div class="border-b md:border-b-0 md:border-r border-slate-100 pb-6 md:pb-0 md:pr-8">
                            <h3 class="font-bold text-sm text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md inline-block mb-3">資産の部</h3>
                            <ul class="divide-y divide-slate-100 text-sm text-slate-700">
                                <li v-for="asset in balanceSheet.assets" :key="asset.name" class="py-2.5 flex justify-between items-center">
                                    <span class="font-medium">{{ asset.name }}</span>
                                    <span class="font-semibold text-slate-900">¥{{ formatNumber(asset.amount) }}</span>
                                </li>
                            </ul>
                            <div class="mt-4 pt-3 border-t-2 border-double border-slate-300 flex justify-between items-center font-bold text-slate-800">
                                <span>資産合計</span>
                                <span class="text-lg">¥{{ formatNumber(totalAmount(balanceSheet.assets)) }}</span>
                            </div>
                        </div>

                        <!-- 右側:負債・純資産の部 -->
                        <div class="md:pl-2">
                            <!-- 負債の部 -->
                            <div class="mb-6">
                                <h3 class="font-bold text-sm text-red-600 bg-red-50 px-2.5 py-1 rounded-md inline-block mb-3">負債の部</h3>
                                <ul class="divide-y divide-slate-100 text-sm text-slate-700">
                                    <li v-for="liability in balanceSheet.liabilities" :key="liability.name" class="py-2.5 flex justify-between items-center">
                                        <span class="font-medium">{{ liability.name }}</span>
                                        <span class="font-semibold text-slate-900">¥{{ formatNumber(liability.amount) }}</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- 純資産の部 -->
                            <div class="mb-4">
                                <h3 class="font-bold text-sm text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md inline-block mb-3">純資産の部</h3>
                                <ul class="divide-y divide-slate-100 text-sm text-slate-700">
                                    <li v-for="equity in balanceSheet.equities" :key="equity.name" class="py-2.5 flex justify-between items-center">
                                        <span class="font-medium">{{ equity.name }}</span>
                                        <span class="font-semibold text-slate-900">¥{{ formatNumber(equity.amount) }}</span>
                                    </li>
                                </ul>
                            </div>

                            <div class="mt-4 pt-3 border-t-2 border-double border-slate-300 flex justify-between items-center font-bold text-slate-800">
                                <span>負債・純資産合計</span>
                                <span class="text-lg">¥{{ formatNumber(totalAmount(balanceSheet.liabilities) + totalAmount(balanceSheet.equities)) }}</span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 💡 簡易損益計算書 (Profit and Loss Statement) -->
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <h2 class="text-lg font-bold mb-4 border-b border-slate-100 pb-3 text-slate-800">簡易損益計算書 (P/L)</h2>
                    <div class="space-y-6">
                        
                        <!-- 収益の部 -->
                        <div>
                            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">売上高（収益）</h3>
                            <ul class="divide-y divide-slate-100 text-sm text-slate-700 bg-slate-50/50 px-4 rounded-xl border border-slate-100">
                                <li v-for="rev in profitAndLoss.revenue" :key="rev.name" class="py-3 flex justify-between items-center">
                                    <span class="font-medium">{{ rev.name }}</span>
                                    <span class="font-semibold text-slate-900">¥{{ formatNumber(rev.amount) }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- 費用の部 -->
                        <div>
                            <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">売上原価・販売管理費（費用）</h3>
                            <ul class="divide-y divide-slate-100 text-sm text-slate-700 bg-slate-50/50 px-4 rounded-xl border border-slate-100">
                                <li v-for="exp in profitAndLoss.expense" :key="exp.name" class="py-3 flex justify-between items-center">
                                    <span class="font-medium">{{ exp.name }}</span>
                                    <span class="font-semibold text-slate-900">¥{{ formatNumber(exp.amount) }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- 当期純利益 -->
                        <div class="pt-4 border-t border-slate-100 flex justify-between items-center font-bold text-slate-800">
                            <span class="text-base">当期純利益</span>
                            <span class="text-xl text-emerald-600 bg-emerald-50 px-3 py-1 rounded-lg">
                                ¥{{ formatNumber(totalAmount(profitAndLoss.revenue) - totalAmount(profitAndLoss.expense)) }}
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>