<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    systemSetting: {
        type: Object,
        required: true,
    },
});

// 💡 既存の登録データをフォームの初期値にセット
const form = useForm({
    company_name: props.systemSetting.company_name || '',
    registration_number: props.systemSetting.registration_number || '',
    postal_code: props.systemSetting.postal_code || '',
    address: props.systemSetting.address || '',
    phone: props.systemSetting.phone || '',
});

// 💡 設定の保存処理（patch送信）
const updateSettings = () => {
    form.patch(route('profile.system-settings.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 border-l-4 border-indigo-600 pl-2">
                テナント自社情報設定（インボイス・マスタ）
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                システムを利用する自社の基本情報および適格請求書発行事業者の登録番号を設定します。
            </p>
        </header>

        <form @submit.prevent="updateSettings" class="space-y-5">
            <!-- 企業名・屋号 -->
            <div>
                <InputLabel for="company_name" value="企業名 / 屋号 *" />
                <TextInput
                    id="company_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.company_name"
                    required
                    autocomplete="organization"
                />
                <InputError class="mt-2" :message="form.errors.company_name" />
            </div>

            <!-- インボイス登録番号 -->
            <div>
                <InputLabel for="registration_number" value="適格請求書発行事業者登録番号 (T番号) *" />
                <div class="relative mt-1">
                    <TextInput
                        id="registration_number"
                        type="text"
                        class="block w-full text-indigo-700 font-mono font-bold tracking-wider"
                        v-model="form.registration_number"
                        required
                        maxlength="14"
                        placeholder="T1234567890123"
                    />
                </div>
                <p class="text-[11px] text-slate-400 mt-1">※「T」から始まる13桁の数字を入力してください（例: T1234567890123）</p>
                <InputError class="mt-2" :message="form.errors.registration_number" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- 郵便番号 -->
                <div>
                    <InputLabel for="postal_code" value="郵便番号" />
                    <TextInput
                        id="postal_code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.postal_code"
                        placeholder="100-0001"
                    />
                    <InputError class="mt-2" :message="form.errors.postal_code" />
                </div>

                <!-- 電話番号 -->
                <div>
                    <InputLabel for="phone" value="電話番号" />
                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        placeholder="03-1234-5678"
                    />
                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>
            </div>

            <!-- 住所 -->
            <div>
                <InputLabel for="address" value="所在地 / 住所" />
                <TextInput
                    id="address"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.address"
                    placeholder="東京都千代田区麹町1-1-1"
                />
                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <!-- 保存ボタンと通知トースト -->
            <div class="flex items-center gap-4 pt-2">
                <PrimaryButton :disabled="form.processing">設定を保存する</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm font-bold text-emerald-600">
                        設定マスタを更新しました。
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>