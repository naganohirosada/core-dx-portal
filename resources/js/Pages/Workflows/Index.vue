<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';

defineProps({
    myWorkflows: Array,
    pendingWorkflows: Array
});

const form = useForm({
    type: 'expense',
    title: '',
    amount: '',
    description: '',
});

const submit = () => {
    form.post(route('workflows.store'), {
        onSuccess: () => form.reset('title', 'amount', 'description'),
    });
};

const handleAction = (id, action) => {
    if (confirm(`この申請を${action === 'approve' ? '承認' : '却下'}しますか？`)) {
        router.patch(route(`workflows.${action}`, id));
    }
};

const statusLabel = (status) => {
    const labels = { pending: '承認待ち', approved: '承認済', rejected: '却下' };
    return labels[status] || status;
};

const statusClass = (status) => {
    const classes = { pending: 'bg-amber-50 text-amber-700', approved: 'bg-emerald-50 text-emerald-700', rejected: 'bg-rose-50 text-rose-700' };
    return classes[status] || 'bg-slate-50';
};
</script>

<template>
    <Head title="ワークフロー申請" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-slate-800">ワークフロー ＆ 稟議精算管理</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-1 h-fit">
                    <h3 class="text-base font-bold text-slate-900 mb-4">各種申請の作成</h3>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">申請種別</label>
                            <select v-model="form.type" class="block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="expense">経費精算申請</option>
                                <option value="ringi">稟議・購買申請</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">タイトル</label>
                            <input v-model="form.title" type="text" required placeholder="品名や目的など" class="block w-full rounded-lg border-slate-200 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">金額 (円・任意)</label>
                            <input v-model="form.amount" type="number" placeholder="12500" class="block w-full rounded-lg border-slate-200 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">詳細・理由</label>
                            <textarea v-model="form.description" rows="4" placeholder="具体的な用途や理由をご記入ください" class="block w-full rounded-lg border-slate-200 text-sm"></textarea>
                        </div>
                        <button type="submit" :disabled="form.processing" class="w-full bg-slate-900 text-white font-medium py-2.5 rounded-lg text-sm hover:bg-slate-800 transition disabled:opacity-50">
                            申請を提出する
                        </button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    
                    <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center">
                            🔑 承認権限：未決裁の申請一覧
                            <span class="ml-2 text-xs bg-rose-50 text-rose-600 px-2 py-0.5 rounded-full font-bold">{{ pendingWorkflows.length }} 件</span>
                        </h3>
                        <div class="space-y-3">
                            <div v-for="wf in pendingWorkflows" :key="wf.id" class="p-4 border border-slate-100 rounded-lg hover:bg-slate-50/50 transition flex justify-between items-center">
                                <div>
                                    <div class="flex items-center space-x-2">
                                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">{{ wf.type === 'expense' ? '経費' : '稟議' }}</span>
                                        <span class="text-xs font-bold text-slate-800">{{ wf.user?.name }} さん</span>
                                        <span v-if="wf.amount" class="text-xs font-mono font-bold text-indigo-600">{{ wf.amount.toLocaleString() }}円</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-900 mt-1">{{ wf.title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ wf.description || '詳細なし' }}</p>
                                </div>
                                <div class="flex space-x-2">
                                    <button @click="handleAction(wf.id, 'reject')" class="bg-slate-100 text-slate-600 text-xs font-bold px-3 py-1.5 rounded-lg hover:bg-rose-50 hover:text-rose-600 transition">
                                        却下
                                    </button>
                                    <button @click="approveNotification(notif)" class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs px-2.5 py-1 rounded shadow-sm hover:bg-indigo-600 transition">
                                        承認
                                    </button>
                                </div>
                            </div>
                            <div v-if="pendingWorkflows.length === 0" class="text-center py-6 text-xs text-slate-400">現在、待機中の決裁申請はありません。</div>
                        </div>
                    </div>

                    <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                        <h3 class="text-base font-bold text-slate-900 mb-4">自分の申請履歴</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-medium">
                                    <tr>
                                        <th class="p-3">種別</th>
                                        <th class="p-3">タイトル</th>
                                        <th class="p-3">金額</th>
                                        <th class="p-3 text-center">ステータス</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr v-for="myWf in myWorkflows" :key="myWf.id" class="hover:bg-slate-50/30">
                                        <td class="p-3 font-bold text-xs">{{ myWf.type === 'expense' ? '経費精算' : '稟議申請' }}</td>
                                        <td class="p-3 font-medium text-slate-900">{{ myWf.title }}</td>
                                        <td class="p-3 font-mono">{{ myWf.amount ? myWf.amount.toLocaleString() + '円' : '-' }}</td>
                                        <td class="p-3 text-center">
                                            <span :class="statusClass(myWf.status)" class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ statusLabel(myWf.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="myWorkflows.length === 0">
                                        <td colspan="4" class="p-6 text-center text-xs text-slate-400">提出済みの申請履歴がありません。</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>