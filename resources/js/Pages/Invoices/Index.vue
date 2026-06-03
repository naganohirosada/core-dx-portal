<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Array,
    projects: Array,
});

const form = useForm({
    project_id: '',
    issue_date: new Date().toISOString().slice(0, 10),
    due_date: '',
    amount: '',
    notes: '',
});

// 💡 案件が選択されたら、その案件の予算(budget)を自動セットする神対応
const onProjectChange = () => {
    const selected = props.projects.find(p => p.id === form.project_id);
    if (selected) {
        form.amount = selected.budget || 0;
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
                        <div class="grid grid-cols-2 gap-2">
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
                            <label class="block text-xs font-medium text-slate-500 mb-1">請求金額 (円)</label>
                            <input v-model="form.amount" type="number" required placeholder="500000" class="block w-full rounded-lg border-slate-200 text-sm" />
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
                                end	</tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>