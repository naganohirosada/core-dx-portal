<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    invoice: Object,
    issuer: Object,
});

// 💡 ブラウザの標準印刷ダイアログを起動（PDF保存もこれで行えます）
const triggerPrint = () => {
    window.print();
};

// 日付を `YYYY年MM月DD日` 形式で返すユーティリティ
const formatDate = (d) => {
    if (!d) return '';
    const dt = new Date(d);
    if (isNaN(dt.getTime())) return d;
    const y = dt.getFullYear();
    const m = String(dt.getMonth() + 1).padStart(2, '0');
    const day = String(dt.getDate()).padStart(2, '0');
    return `${y}年${m}月${day}日`;
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
                <div class="text-right">
                    <p class="font-bold text-lg">{{ issuer.company_name }}</p>
                    <p class="text-sm text-gray-600">登録番号: {{ issuer.registration_number }}</p>
                    <p class="text-sm text-gray-600">〒{{ issuer.postal_code }} {{ issuer.address }}</p>
                    <p class="text-sm text-gray-600">TEL: {{ issuer.phone }}</p>
                    <p class="text-sm mt-4">請求書番号: {{ invoice.invoice_number }}</p>
                    <p class="text-sm">取引日: {{ formatDate(invoice.transaction_date || invoice.issue_date) }}</p>
                    <p class="text-sm">発行日: {{ formatDate(invoice.issue_date) }}</p>
                    <p class="text-sm">支払期限日: {{ formatDate(invoice.due_date) }}</p>
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
                    <tr v-for="it in invoice.items" :key="it.id" class="border-b border-slate-300">
                        <td class="border-r border-slate-300 p-4 font-medium">{{ it.description || invoice.project?.name }}</td>
                        <td class="border-r border-slate-300 p-4 text-right font-mono">{{ it.quantity }}</td>
                        <td class="border-r border-slate-300 p-4 text-right font-mono">{{ Number(it.unit_price).toLocaleString() }}</td>
                        <td class="p-4 text-right font-mono font-bold">{{ Number(it.subtotal).toLocaleString() }}</td>
                    </tr>
                </tbody>
            </table>

            <div class="max-w-md ml-auto mt-4 text-sm">
                <div class="flex justify-between py-1 text-slate-700"><span>小計（10%）</span><span class="font-mono">￥{{ (invoice.subtotal_10_percent || 0).toLocaleString() }}</span></div>
                <div class="flex justify-between py-1 text-slate-700"><span>消費税（10%）</span><span class="font-mono">￥{{ (invoice.tax_amount_10_percent || 0).toLocaleString() }}</span></div>
                <div class="flex justify-between py-1 text-slate-700"><span>小計（8%）</span><span class="font-mono">￥{{ (invoice.subtotal_8_percent || 0).toLocaleString() }}</span></div>
                <div class="flex justify-between py-1 text-slate-700"><span>消費税（8%）</span><span class="font-mono">￥{{ (invoice.tax_amount_8_percent || 0).toLocaleString() }}</span></div>
                <div class="border-t border-slate-300 mt-2 pt-2 flex justify-between text-lg font-bold"><span>合計（税込）</span><span class="font-mono">￥{{ Number(invoice.amount).toLocaleString() }}</span></div>
            </div>

            <div class="border border-slate-300 p-4 rounded-lg print:rounded-none text-xs bg-slate-50/50">
                <h4 class="font-bold text-slate-700 mb-1.5">【備考 / 振込口座案内】</h4>
                <p class="text-slate-600 leading-relaxed whitespace-pre-wrap">{{ invoice.notes || 'お振込み手数料は貴社にてご負担願います。' }}</p>
            </div>

        </div>
    </div>
</template>