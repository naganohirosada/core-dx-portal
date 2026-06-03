<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';

const form = useForm({
    name: '',
    postal_code: '',
    address: '',
    tel: '',
});

// 💡 郵便番号から住所を自動検索する関数
const searchAddress = async () => {
    const code = form.postal_code.replace('-', '');
    if (code.length !== 7) return;

    try {
        // 💡 外部ではなく、自社のLaravelのルートを叩く
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
    form.post(route('companies.store'));
};
</script>

<template>
    <Head title="顧客登録" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-slate-800">顧客アカウント新規登録</h2>
                <Link :href="route('companies.index')" class="text-sm text-slate-500 hover:text-slate-800">← 一覧に戻る</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm border border-slate-100 rounded-xl p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">企業名 <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="株式会社Coredesia" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">郵便番号 (7桁)</label>
                                <div class="mt-1 flex space-x-2">
                                    <input v-model="form.postal_code" @input="searchAddress" type="text" maxlength="8" class="block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="100-0001" />
                                </div>
                                <p class="text-xs text-slate-400 mt-1">※ハイフンを入力するか、7桁入力すると自動検索します</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">電話番号</label>
                                <input v-model="form.tel" type="text" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="03-XXXX-XXXX" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700">住所</label>
                            <input v-model="form.address" type="text" class="mt-1 block w-full rounded-lg border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="東京都千代田区..." />
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" :disabled="form.processing" class="bg-slate-900 text-white px-5 py-2.5 rounded-lg text-sm font-medium shadow-sm hover:bg-slate-800 transition disabled:opacity-50">
                                顧客情報を保存する
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>