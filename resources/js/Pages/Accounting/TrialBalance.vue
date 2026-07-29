<!-- resources/js/Pages/Accounting/TrialBalance.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    trialBalance: Array
});

const formatNumber = (num) => {
    return Number(num).toLocaleString();
};
</script>

<template>
    <Head title="合計残高試算表" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">財務管理</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-1">合計残高試算表</h2>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <!-- 💡 試算表データテーブル -->
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl overflow-hidden">
                    <h2 class="text-lg font-bold mb-4 text-slate-800">勘定科目別集計一覧</h2>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-100 text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-medium">
                                    <th class="border border-slate-100 px-4 py-3 text-left w-32">科目コード</th>
                                    <th class="border border-slate-100 px-4 py-3 text-left">勘定科目</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-48">借方合計</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-48">貸方合計</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-48">差引残高</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700">
                                <tr v-for="item in trialBalance" :key="item.code" class="hover:bg-slate-50/50 transition">
                                    <td class="border border-slate-100 px-4 py-3 font-mono text-slate-400">{{ item.code }}</td>
                                    <td class="border border-slate-100 px-4 py-3 font-semibold text-slate-800">{{ item.name }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-medium text-blue-600 bg-blue-50/10">
                                        ¥{{ formatNumber(item.debit_total) }}
                                    </td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-medium text-red-600 bg-red-50/10">
                                        ¥{{ formatNumber(item.credit_total) }}
                                    </td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-bold text-slate-900 bg-slate-50/30">
                                        ¥{{ formatNumber(item.balance) }}
                                    </td>
                                </tr>
                                
                                <!-- データが空の場合のケア -->
                                <tr v-if="!trialBalance || trialBalance.length === 0">
                                    <td colspan="5" class="text-center py-8 text-slate-400 bg-slate-50/30">
                                        登録された仕訳データがありません。
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>