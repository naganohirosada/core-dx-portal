<script setup>
import { ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import UpdateSystemSettingsForm from './Partials/UpdateSystemSettingForm.vue';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    systemSetting: Object,
});

// 💡 1. ログインユーザーの情報を取得
const user = usePage().props.auth.user;

// 💡 2. 権限チェック（自社情報の編集権限があるか判定）
// role カラムが admin または manager の場合のみ true になります
const canManageSystem = ['admin', 'manager'].includes(user.role);

// 💡 3. 現在アクティブなタブを管理するステート（初期値はアカウント情報）
const activeTab = ref('profile');
</script>

<template>
    <Head title="システムアカウント設定" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                各種システム・アカウント設定
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- 🗂️ ユーザー権限対応・モダンタブメニュー -->
                <div class="border-b border-gray-200 bg-white px-6 py-2 rounded-t-xl shadow-xs flex flex-wrap gap-2">
                    <!-- タブ1：アカウント基本情報 -->
                    <button 
                        @click="activeTab = 'profile'"
                        :class="activeTab === 'profile' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-3 pt-2 px-4 border-b-2 text-sm transition-all duration-200"
                    >
                        👤 アカウント情報
                    </button>

                    <!-- タブ2：パスワード変更 -->
                    <button 
                        @click="activeTab = 'password'"
                        :class="activeTab === 'password' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-3 pt-2 px-4 border-b-2 text-sm transition-all duration-200"
                    >
                        🔒 パスワード変更
                    </button>

                    <!-- タブ3：テナント自社情報設定（★★★ 管理者・マネージャー限定 ★★★） -->
                    <!-- v-if で一般社員にはタブの存在自体を完全に隠蔽・ガードします -->
                    <button 
                        v-if="canManageSystem"
                        @click="activeTab = 'system'"
                        :class="activeTab === 'system' ? 'border-indigo-600 text-indigo-600 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-3 pt-2 px-4 border-b-2 text-sm transition-all duration-200"
                    >
                        ⚙️ テナント自社情報設定
                    </button>

                    <!-- タブ4：危険ゾーン（アカウント削除） -->
                    <button 
                        @click="activeTab = 'danger'"
                        :class="activeTab === 'danger' ? 'border-rose-600 text-rose-600 font-bold' : 'border-transparent text-gray-500 hover:text-rose-500 hover:border-gray-300'"
                        class="whitespace-nowrap pb-3 pt-2 px-4 border-b-2 text-sm transition-all duration-200 md:ml-auto"
                    >
                        ⚠️ アカウント削除
                    </button>
                </div>

                <!-- 📜 各タブのコンテンツコンテンツエリア -->
                <div class="bg-white p-6 shadow sm:rounded-b-xl sm:p-8 min-h-[400px]">
                    
                    <!-- ① アカウント基本情報コンテンツ -->
                    <div v-show="activeTab === 'profile'" class="transition-opacity duration-300">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>

                    <!-- ② パスワード変更コンテンツ -->
                    <div v-show="activeTab === 'password'" class="transition-opacity duration-300">
                        <UpdatePasswordForm class="max-w-xl" />
                    </div>

                    <!-- ③ 自社インボイス情報設定（★★★ 権限ガード ★★★） -->
                    <!-- 表示エリアも v-f で厳格に囲むことで、不正なjsインジェクションをサーバーサイドレベルで防御します -->
                    <div v-if="canManageSystem" v-show="activeTab === 'system'" class="transition-opacity duration-300">
                        <UpdateSystemSettingsForm
                            :system-setting="systemSetting"
                            class="max-w-xl"
                        />
                    </div>

                    <!-- ④ アカウント削除コンテンツ -->
                    <div v-show="activeTab === 'danger'" class="transition-opacity duration-300">
                        <DeleteUserForm class="max-w-xl" />
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>