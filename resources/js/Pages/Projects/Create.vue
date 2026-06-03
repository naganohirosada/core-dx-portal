<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    companies: Array
});

const form = useForm({
    company_id: '',
    name: '',
    start_date: '',
    end_date: '',
    budget: '',
});

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <Head title="案件登録" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">新規案件の登録</h2>
                <Link :href="route('projects.index')" class="text-sm text-slate-500 hover:text-slate-800">← 一覧に戻る</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">対象顧客 <span class="text-red-500">*</span></label>
                            <select v-model="form.company_id" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="" disabled>顧客を選択してください</option>
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">案件名 <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="DXポータル構築プロジェクト" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">開始予定日</label>
                                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">終了予定日</label>
                                <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">案件予算 (円)</label>
                            <input v-model="form.budget" type="number" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="1500000" />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing" class="bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-sm hover:bg-slate-800 transition disabled:opacity-50">
                                案件を登録する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>