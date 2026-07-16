<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="ログイン | Core DX Portal" />

    <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans selection:bg-indigo-600">
        <div class="sm:mx-auto w-full max-w-md text-center space-y-4">
            <!-- ブランドロゴエリア -->
            <div class="inline-flex items-center justify-center size-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 text-2xl shadow-lg">
                🏢
            </div>
            <h2 class="text-3xl font-black tracking-widest text-white">Core DX Portal</h2>
            <p class="text-sm text-slate-400">マルチテナント型 統合ビジネスオペレーションSaaS</p>
        </div>

        <div class="mt-8 sm:mx-auto w-full max-w-md px-4 sm:px-0">
            <div class="bg-slate-900 border border-slate-800 py-8 px-6 shadow-2xl rounded-2xl space-y-6 sm:px-10">
                
                <div v-if="status" class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-sm font-medium text-emerald-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- メールアドレス -->
                    <div>
                        <InputLabel for="email" value="メールアドレス" class="text-slate-300 font-medium text-xs mb-1" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="corp@example.com"
                        />
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <!-- パスワード -->
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <InputLabel for="password" value="パスワード" class="text-slate-300 font-medium text-xs" />
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition">
                                パスワードをお忘れですか？
                            </Link>
                        </div>
                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full bg-slate-950 border-slate-800 text-slate-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- ログイン状態を保持 -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center group cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" class="bg-slate-950 border-slate-800 text-indigo-600 rounded-md focus:ring-offset-slate-900" />
                            <span class="ms-2 text-xs text-slate-400 group-hover:text-slate-300 transition">サインイン状態を維持する</span>
                        </label>
                    </div>

                    <!-- ログインボタン -->
                    <div class="pt-2">
                        <PrimaryButton
                            class="w-full justify-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3 px-4 rounded-xl shadow-md transition focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-slate-900 border-none text-sm tracking-wider"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                            ポータルにサインイン
                        </PrimaryButton>
                    </div>
                </form>

                <div class="border-t border-slate-800/80 pt-4 text-center">
                    <p class="text-xs text-slate-500">
                        アカウントをお持ちでないですか？
                        <Link :href="route('register')" class="font-bold text-indigo-400 hover:text-indigo-300 ml-1 transition">
                            新規登録（無料）
                        </Link>
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>