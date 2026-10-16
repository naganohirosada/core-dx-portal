<!-- resources/js/Pages/Accounting/GeneralLedger.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    accounts: Array,
    selectedAccount: Object,
    rows: Array,
});

const formatNumber = (num) => Number(num).toLocaleString();

const changeAccount = (event) => {
    router.get(route('accounting.general-ledger'), { account_id: event.target.value }, { preserveState: true });
};
</script>

<template>
    <Head title="総勘定元帳・出納帳" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">財務管理</span>
                <h2 class="text-xl font-semibold text-slate-800 mt-1">総勘定元帳・出納帳</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">勘定科目（現金・普通預金を選ぶと出納帳として利用できます）</label>
                            <select :value="selectedAccount?.id" @change="changeAccount" class="block w-72 rounded-lg border-slate-200 text-sm">
                                <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} {{ a.name }}</option>
                            </select>
                        </div>
                        <p v-if="rows.length" class="text-sm text-slate-600">
                            期末残高: <strong class="text-slate-900 font-mono">¥{{ formatNumber(rows[rows.length - 1].balance) }}</strong>
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-100 text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-medium">
                                    <th class="border border-slate-100 px-4 py-3 text-left w-32">日付</th>
                                    <th class="border border-slate-100 px-4 py-3 text-left">摘要</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-40">借方</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-40">貸方</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-40">残高</th>
                                </tr>
                            </thead>
                            <tbody class="text-slate-700">
                                <tr v-for="row in rows" :key="row.id" class="hover:bg-slate-50/50">
                                    <td class="border border-slate-100 px-4 py-3 font-mono text-xs">{{ row.date }}</td>
                                    <td class="border border-slate-100 px-4 py-3">{{ row.description }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right text-blue-600">{{ row.debit ? '¥' + formatNumber(row.debit) : '' }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right text-red-600">{{ row.credit ? '¥' + formatNumber(row.credit) : '' }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-bold text-slate-900">¥{{ formatNumber(row.balance) }}</td>
                                </tr>
                                <tr v-if="rows.length === 0">
                                    <td colspan="5" class="text-center py-8 text-slate-400 bg-slate-50/30">この勘定科目の仕訳はありません。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
