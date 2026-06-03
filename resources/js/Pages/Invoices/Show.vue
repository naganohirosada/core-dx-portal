<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    invoice: Object,
});

// 💡 ブラウザの標準印刷ダイアログを起動（PDF保存もこれで行えます）
const triggerPrint = () => {
    window.print();
};
</script>

<template>
    <Head :title="'請求書_' + invoice.invoice_number" />
    
    <div class="bg-slate-100 print:bg-white min-h-screen py-8 print:py-0 text-slate-800">
        
        <div class="max-w-4xl mx-auto mb-6 px-4 flex justify-between items-center print:hidden">
            <Link :href="route('invoices.index')" class="text-sm font-medium text-slate-600 hover:text-slate-900">
                ← 請求書一覧に戻る
            </Link>
            <button @click="triggerPrint" class="bg-indigo-600 text-white font-bold px-5 py-2 rounded-xl shadow-md hover:bg-indigo-500 transition text-sm flex items-center">
                🖨️ この請求書を印刷 / PDF保存
            </button>
        </div>

        <div class="max-w-4xl mx-auto bg-white shadow-xl print:shadow-none border print:border-none border-slate-200 rounded-xl print:rounded-none p-12 print:p-0 font-sans">
            
            <h1 class="text-3xl font-bold tracking-widest text-center border-b-2 border-slate-950 pb-2 mb-10">請 求 書</h1>
            
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                    <h2 class="text-lg font-bold border-b border-slate-400 pb-1 mb-2">
                        {{ invoice.project?.company?.name }} 御中
                    </h2>
                    <p class="text-xs text-slate-500">下記の通り、金額をご請求申し上げます。</p>
                </div>
                <div class="text-right text-xs space-y-1">
                    <p class="text-sm font-mono font-bold">請求番号: {{ invoice.invoice_number }}</p>
                    <p>発行日: {{ invoice.issue_date.slice(0,10) }}</p>
                    <p>支払期日: {{ invoice.due_date.slice(0,10) }}</p>
                    <div class="pt-3 font-medium text-left inline-block">
                        <p class="font-bold text-sm">株式会社 Coredesia</p>
                        <p>〒100-0001 東京都千代田区千代田1-1</p>
                        <p>TEL: 03-1234-5678</p>
                    </div>
                </div>
            </div>

            <div class="bg-slate-900 text-white p-4 rounded-lg print:rounded-none flex justify-between items-center mb-8">
                <span class="text-sm font-bold tracking-wider pl-2">御請求金額合計 (税込)</span>
                <span class="text-2xl font-mono font-bold pr-2">￥{{ invoice.amount.toLocaleString() }} -</span>
            </div>

            <table class="w-full text-sm border-collapse border border-slate-300 mb-8">
                <thead class="bg-slate-50">
                    <tr class="border-b border-slate-300 text-xs">
                        <th class="border-r border-slate-300 p-3 text-left">品名・項目</th>
                        <th class="border-r border-slate-300 p-3 text-right w-24">数量</th>
                        <th class="border-r border-slate-300 p-3 text-right w-32">単価</th>
                        <th class="p-3 text-right w-32">金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-300">
                        <td class="border-r border-slate-300 p-4 font-medium">
                            {{ invoice.project?.name }} に伴う業務一式
                        </td>
                        <td class="border-r border-slate-300 p-4 text-right font-mono">1</td>
                        <td class="border-r border-slate-300 p-4 text-right font-mono">{{ invoice.amount.toLocaleString() }}</td>
                        <td class="p-4 text-right font-mono font-bold">{{ invoice.amount.toLocaleString() }}</td>
                    </tr>
                    <tr v-for="i in 3" :key="i" class="border-b border-slate-200 h-10">
                        <td class="border-r border-slate-300 p-2"></td>
                        <td class="border-r border-slate-300 p-2"></td>
                        <td class="border-r border-slate-300 p-2"></td>
                        <td class="p-2"></td>
                    </tr>
                </tbody>
            </table>

            <div class="border border-slate-300 p-4 rounded-lg print:rounded-none text-xs bg-slate-50/50">
                <h4 class="font-bold text-slate-700 mb-1.5">【備考 / 振込口座案内】</h4>
                <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ invoice.notes || 'お振込み手数料は貴社にてご負担願います。' }}</p>
            </div>

        </div>
    </div>
</template>