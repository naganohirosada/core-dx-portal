<!-- resources/js/Pages/Users/Index.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    users: Array,
    maxUsers: Number,
});

const page = usePage();
const errors = computed(() => page.props.errors || {});
const isFull = computed(() => props.users.length >= props.maxUsers);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'general',
    paid_leave_days: 10,
});

const submit = () => {
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const roleLabels = { admin: '管理者', manager: 'マネージャー', general: '一般社員' };

const updateUser = (user, field, value) => {
    router.patch(route('users.update', user.id), {
        role: field === 'role' ? value : user.role,
        paid_leave_days: field === 'paid_leave_days' ? Number(value) : user.paid_leave_days,
    }, { preserveScroll: true });
};
</script>

<template>
    <Head title="社員アカウント管理" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">社員アカウント管理</h2>
                <span class="text-sm font-medium px-3 py-1 rounded-lg" :class="isFull ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600'">
                    利用ID数 {{ users.length }} / {{ maxUsers }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl h-fit">
                    <h3 class="text-base font-bold text-slate-900 mb-4">社員を追加</h3>
                    <p v-if="isFull" class="text-xs text-rose-600 mb-3">契約上のユーザー数上限に達しています。追加するにはID数の追加契約が必要です。</p>
                    <form @submit.prevent="submit" class="space-y-3">
                        <input v-model="form.name" type="text" required placeholder="氏名" class="block w-full rounded-lg border-slate-200 text-sm" />
                        <input v-model="form.email" type="email" required placeholder="メールアドレス（ログインID）" class="block w-full rounded-lg border-slate-200 text-sm" />
                        <input v-model="form.password" type="password" required placeholder="初期パスワード（8文字以上）" class="block w-full rounded-lg border-slate-200 text-sm" />
                        <div class="grid grid-cols-2 gap-2">
                            <select v-model="form.role" class="block w-full rounded-lg border-slate-200 text-sm">
                                <option value="general">一般社員</option>
                                <option value="manager">マネージャー</option>
                                <option value="admin">管理者</option>
                            </select>
                            <input v-model.number="form.paid_leave_days" type="number" min="0" max="40" class="block w-full rounded-lg border-slate-200 text-sm" title="有給付与日数" />
                        </div>
                        <p class="text-[11px] text-slate-400">右の数値は有給休暇の付与日数です。</p>
                        <p v-for="(msg, key) in form.errors" :key="key" class="text-xs text-rose-600">{{ msg }}</p>
                        <button type="submit" :disabled="form.processing || isFull" class="w-full bg-slate-900 text-white font-medium py-2.5 rounded-lg text-sm hover:bg-slate-800 transition disabled:opacity-40">
                            追加する
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-2">
                    <h3 class="text-base font-bold text-slate-900 mb-4">社員一覧</h3>
                    <p v-if="errors.role" class="text-xs text-rose-600 mb-2">{{ errors.role }}</p>
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 text-xs font-medium">
                            <tr>
                                <th class="p-3">氏名</th>
                                <th class="p-3">メールアドレス</th>
                                <th class="p-3">権限</th>
                                <th class="p-3 text-right">有給付与日数</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <tr v-for="u in users" :key="u.id">
                                <td class="p-3 font-semibold text-slate-900">{{ u.name }}</td>
                                <td class="p-3">{{ u.email }}</td>
                                <td class="p-3">
                                    <select :value="u.role" @change="updateUser(u, 'role', $event.target.value)" class="text-xs py-1 rounded-md border-slate-200">
                                        <option v-for="(label, key) in roleLabels" :key="key" :value="key">{{ label }}</option>
                                    </select>
                                </td>
                                <td class="p-3 text-right">
                                    <input type="number" min="0" max="40" :value="u.paid_leave_days" @change="updateUser(u, 'paid_leave_days', $event.target.value)" class="w-20 text-xs py-1 rounded-md border-slate-200 text-right" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
