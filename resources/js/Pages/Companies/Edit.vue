<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    company: Object
});

const form = useForm({
    name: props.company.name,
    postal_code: props.company.postal_code || '',
    address: props.company.address || '',
    tel: props.company.tel || '',
    status: props.company.status,
});

const searchAddress = async () => {
    const code = form.postal_code.replace('-', '');
    if (code.length !== 7) return;

    try {
        const response = await axios.get(route('postal-code.search', code));
        if (response.data && response.data.results) {
            const result = response.data.results[0];
            form.address = result.address1 + result.address2 + result.address3;
        }
    } catch (error) {
        console.error('郵便番号検索に失敗しました', error);
    }
};

const submit = () => {
    form.patch(route('companies.update', props.company.id));
};
</script>

<template>
    <Head title="顧客編集" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">顧客情報の編集</h2>
                <Link :href="route('companies.index')" class="text-sm text-slate-500 hover:text-slate-800">← キャンセル</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">企業名 <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">郵便番号</label>
                                <input v-model="form.postal_code" @input="searchAddress" type="text" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">電話番号</label>
                                <input v-model="form.tel" type="text" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">住所</label>
                            <input v-model="form.address" type="text" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">取引ステータス</label>
                            <select v-model="form.status" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="active">有効 (Active)</option>
                                <option value="inactive">無効 (Inactive)</option>
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