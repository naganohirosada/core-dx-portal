<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    companies: Array
});

const form = useForm({
    company_id: props.project.company_id,
    name: props.project.name,
    start_date: props.project.start_date || '',
    end_date: props.project.end_date || '',
    budget: props.project.budget || '',
    status: props.project.status,
});

const submit = () => {
    form.patch(route('projects.update', props.project.id));
};
</script>

<template>
    <Head title="案件編集" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">案件情報の編集</h2>
                <Link :href="route('projects.index')" class="text-sm text-slate-500 hover:text-slate-800">← キャンセル</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">対象顧客 <span class="text-red-500">*</span></label>
                            <select v-model="form.company_id" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option v-for="company in companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">案件名 <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">開始日</label>
                                <input v-model="form.start_date" type="date" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">終了日</label>
                                <input v-model="form.end_date" type="date" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">案件予算 (円)</label>
                            <input v-model="form.budget" type="number" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">ステータス</label>
                            <select v-model="form.status" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="lead">リード (商談中)</option>
                                <option value="ongoing">進行中 (Ongoing)</option>
                                <option value="completed">完了 (Completed)</option>
                                <option value="suspended">中断 (Suspended)</option>
                            </select>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-sm hover:bg-indigo-500 transition disabled:opacity-50">
                                変更を保存する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>