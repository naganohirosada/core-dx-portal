<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    invoices: Array,
    projects: Array,
});

const form = useForm({
    project_id: '',
    transaction_date: new Date().toISOString().slice(0, 10),
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    notes: '',
    items: [
        { description: '', quantity: 1, unit_price: 0, tax_rate: 10 }
    ],
});

const totalAmount = computed(() => {
    let subtotal10 = 0;
    let subtotal8 = 0;
    let subtotal0 = 0;
    form.items.forEach(i => {
        const s = Number(i.quantity || 0) * Number(i.unit_price || 0);
        if (Number(i.tax_rate) === 10) subtotal10 += s;
        else if (Number(i.tax_rate) === 8) subtotal8 += s;
        else subtotal0 += s;
    });
    const tax10 = Math.floor(subtotal10 * 0.10);
    const tax8 = Math.floor(subtotal8 * 0.08);
    return subtotal10 + subtotal8 + subtotal0 + tax10 + tax8;
});

// 💡 案件が選択されたら、その案件の予算(budget)を自動セットする神対応
const onProjectChange = () => {
    const selected = props.projects.find(p => p.id === form.project_id);
    if (selected) {
        // 案件予算がある場合は最初の明細の単価にセット
        if (form.items && form.items.length) {
            form.items[0].unit_price = selected.budget || 0;
        }
        // 支払期日を発行日の1ヶ月後に自動セット
        const issue = new Date(form.issue_date);
        issue.setMonth(issue.getMonth() + 1);
        form.due_date = issue.toISOString().slice(0, 10);
    }
};

const submit = () => {
    form.post(route('invoices.store'), {
        onSuccess: () => form.reset(),
    });
};

const updateStatus = (id, status) => {
    router.patch(route('invoices.status.update', id), { status: status });
};

const statusLabel = (status) => {
    const labels = { draft: '下書き', sent: '請求済', paid: '入金済' };
    return labels[status] || status;
};
</script>

<template>
    <Head title="請求書管理" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-slate-800">請求書発行 ➔ 売上管理</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl h-fit lg:col-span-1">
                    <h3 class="text-base font-bold text-slate-900 mb-4">新規請求書の作成</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">対象の案件プロジェクト</label>
                            <select v-model="form.project_id" @change="onProjectChange" required class="block w-full rounded-lg border-slate-200 text-sm">
                                <option value="" disabled>案件を選択してください</option>
                                <option v-for="p in projects" :key="p.id" :value="p.id">
                                    [{{ p.company?.name }}] {{ p.name }}
                                </option>
                            </select>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">取引日</label>
                                <input v-model="form.transaction_date" type="date" required class="block w-full rounded-lg border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">発行日</label>
                                <input v-model="form.issue_date" type="date" required class="block w-full rounded-lg border-slate-200 text-xs" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">支払期日</label>
                                <input v-model="form.due_date" type="date" required class="block w-full rounded-lg border-slate-200 text-xs" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">明細</label>
                            <div class="space-y-2">
                                <div v-for="(it, idx) in form.items" :key="idx" class="grid grid-cols-12 gap-2 items-end">
                                    <div class="col-span-5">
                                        <input v-model="it.description" placeholder="品目" class="block w-full rounded-lg border-slate-200 text-sm" />
                                    </div>
                                    <div class="col-span-2">
                                        <input v-model.number="it.quantity" type="number" min="1" class="block w-full rounded-lg border-slate-200 text-sm" />
                                    </div>
                                    <div class="col-span-2">
                                        <input v-model.number="it.unit_price" type="number" min="0" class="block w-full rounded-lg border-slate-200 text-sm" />
                                    </div>
                                    <div class="col-span-2">
                                        <select v-model.number="it.tax_rate" class="block w-full rounded-lg border-slate-200 text-sm">
                                            <option :value="10">税率 10%</option>
                                            <option :value="8">税率 8%</option>
                                            <option :value="0">非課税</option>
                                        </select>
                                    </div>
                                    <div class="col-span-1">
                                        <button type="button" @click.prevent="form.items.splice(idx,1)" class="text-red-600">削除</button>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" @click.prevent="form.items.push({ description: '', quantity: 1, unit_price: 0, tax_rate: 10 })" class="text-indigo-600">＋ 明細を追加</button>
                                </div>
                            </div>

                            <div class="mt-2 text-right font-mono font-bold text-slate-900">合計: {{ totalAmount.toLocaleString() }}円（税計算済）</div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">特記・備考</label>
                            <textarea v-model="form.notes" rows="3" placeholder="振込先口座など" class="block w-full rounded-lg border-slate-200 text-xs"></textarea>
                        </div>
                        <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 text-white font-medium py-2.5 rounded-lg text-sm hover:bg-slate-800 transition">
                            請求書を生成する
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-2">
                    <h3 class="text-base font-bold text-slate-900 mb-4">請求書発行履歴</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 text-xs font-medium uppercase">
                                <tr>
                                    <th class="p-3">請求番号</th>
                                    <th class="p-3">請求先顧客 / 案件</th>
                                    <th class="p-3 text-right">金額</th>
                                    <th class="p-3 text-center">ステータス</th>
                                    <th class="p-3 text-center">操作</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-slate-50/50 transition">
                                    <td class="p-3 font-mono text-xs font-bold text-slate-900">{{ inv.invoice_number }}</td>
                                    <td class="p-3">
                                        <p class="font-semibold text-slate-800 text-xs">{{ inv.project?.company?.name }}</p>
                                        <p class="text-slate-500 text-[11px] mt-0.5">{{ inv.project?.name }}</p>
                                    </td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">{{ inv.amount.toLocaleString() }}円</td>
                                    <td class="p-3 text-center">
                                        <select :value="inv.status" @change="updateStatus(inv.id, $event.target.value)" class="text-xs py-1 rounded-md border-slate-200 font-medium">
                                            <option value="draft">下書き</option>
                                            <option value="sent">請求済</option>
                                            <option value="paid">🎉 入金済</option>
                                        </select>
                                    </td>
                                    <td class="p-3 text-center">
                                        <Link :href="route('invoices.show', inv.id)" class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1.5 rounded-lg hover:bg-indigo-600 hover:text-white transition">
                                            表示・印刷
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="invoices.length === 0">
                                    <td colspan="5" class="p-6 text-center text-xs text-slate-400">作成済みの請求書はありません。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>