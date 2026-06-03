<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    tasks: Array,
    notifications: Array,
    workTimeData: Object,
    today: String,
    currentStatus: String
});

// 💡 ボタンが押されたら、Inertiaでサーバーに打刻タイプを送信
const punch = (type) => {
    router.post(route('attendance.punch'), { type: type }, {
        preserveScroll: true, // 画面のスクロール位置をキープしたまま更新
    });
};

const leaveForm = useForm({
    start_date: '',
    end_date: '',
    reason: '',
});

// 有給を申請する関数
const submitLeave = () => {
    leaveForm.post(route('paid-leaves.store'), {
        onSuccess: () => leaveForm.reset(),
    });
};

// 申請を承認する関数
const approveLeave = (leaveId) => {
    if (confirm('この有給申請を承認しますか？ カレンダーへ自動登録されます。')) {
        router.patch(route('paid-leaves.approve', leaveId));
    }
};

// 💡 勤怠の打刻状態を管理するローカルステート（後でバックエンドと連動）
const attendanceStatus = ref('未出勤'); // 未出勤, 勤務中, 休憩中, 退勤済

const clockIn = () => { attendanceStatus.value = '勤務中'; alert('出勤を打刻しました！'); };
const clockOut = () => { attendanceStatus.value = '退勤済'; alert('退勤を打刻しました！'); };
const breakStart = () => { attendanceStatus.value = '休憩中'; alert('休憩を開始しました！'); };
const breakEnd = () => { attendanceStatus.value = '勤務中'; alert('休憩を終了しました！'); };
</script>

<template>
    <Head title="ダッシュボード" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                <div>
                    <h2 class="text-xl font-semibold text-slate-800 leading-tight">総合ダッシュボード</h2>
                    <p class="text-xs text-slate-500 mt-1">こんにちは、{{ $page.props.auth.user.name }} さん</p>
                </div>
                <div class="text-sm font-medium text-slate-600 bg-slate-100 px-4 py-2 rounded-lg">
                    📅 今日: {{ today }}
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Attendance</span>
                            <h3 class="text-lg font-bold text-slate-800 mt-0.5">リアルタイム勤怠打刻</h3>
                            <div class="mt-2 flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full animate-ping" :class="attendanceStatus === '勤務中' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                <p class="text-sm font-semibold text-slate-600">現在のステータス: 
                                    <span class="text-slate-900 font-bold" :class="{'text-emerald-600': attendanceStatus==='勤務中', 'text-amber-500': attendanceStatus==='休憩中'}">{{ attendanceStatus }}</span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:flex sm:space-x-3 gap-3">
                            <button @click="punch('clock_in')" :disabled="currentStatus !== '未出勤'" class="flex-1 sm:flex-none bg-emerald-600 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:bg-emerald-500 transition text-sm disabled:opacity-30">
                                🛫 出勤
                            </button>
                            <button @click="punch('break_start')" :disabled="currentStatus !== '勤務中'" class="flex-1 sm:flex-none bg-amber-500 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:bg-amber-400 transition text-sm disabled:opacity-30">
                                ☕ 休憩開始
                            </button>
                            <button @click="punch('break_end')" :disabled="currentStatus !== '休憩中'" class="flex-1 sm:flex-none bg-amber-600 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:bg-amber-500 transition text-sm disabled:opacity-30">
                                休憩終了
                            </button>
                            <button @click="punch('clock_out')" :disabled="currentStatus !== '勤務中'" class="flex-1 sm:flex-none bg-slate-900 text-white font-medium px-6 py-3 rounded-xl shadow-sm hover:bg-slate-800 transition text-sm disabled:opacity-30">
                                🛬 退勤
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-6 lg:col-span-1">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-slate-800 flex items-center">
                                📋 進行中のタスク
                            </h4>
                            <Link :href="route('projects.index')" class="text-xs text-indigo-600 hover:underline">案件一覧 ➔</Link>
                        </div>
                        <div class="space-y-3">
                            <div v-for="task in tasks" :key="task.id" class="p-3 bg-slate-50 border border-slate-100 rounded-lg hover:border-indigo-200 transition">
                                <p class="text-sm font-semibold text-slate-800">{{ task.title }}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-blue-50 text-blue-600">{{ task.status }}</span>
                                    <span v-if="task.due_date" class="text-[11px] text-slate-400 font-mono">⏳ {{ task.due_date }}</span>
                                </div>
                            </div>
                            <div v-if="tasks.length === 0" class="text-center py-8 text-sm text-slate-400">
                                現在、進行中のタスクはありません。
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-6 lg:col-span-1">
                        <h4 class="font-bold text-slate-800 mb-4">📊 今月の稼働時間統計</h4>
                        <div class="space-y-5">
                            <div v-for="(label, index) in workTimeData.labels" :key="index" class="space-y-1.5">
                                <div class="flex justify-between text-xs font-medium text-slate-600">
                                    <span>{{ label }}</span>
                                    <span>個人: <strong class="text-slate-900">{{ workTimeData.personal[index] }}h</strong> / 全社平均: {{ workTimeData.team_avg[index] }}h</span>
                                </div>
                                <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden space-y-0 relative">
                                    <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" :style="{ width: (workTimeData.personal[index] * 2) + '%' }"></div>
                                    <div class="absolute top-0 h-full w-0.5 bg-rose-400" :style="{ left: (workTimeData.team_avg[index] * 2) + '%' }" title="全社平均"></div>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                                <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-indigo-600 mr-1"></span> 個人稼働時間</span>
                                <span class="flex items-center"><span class="w-2 h-0.5 bg-rose-400 mr-1"></span> 全社平均ライン</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-6 lg:col-span-1">
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="font-bold text-slate-800 flex items-center">
                                🔔 承認待ちのワークフロー
                            </h4>
                            <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ notifications.length }} 件</span>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-white border border-slate-100 shadow-sm rounded-xl p-6 lg:col-span-1">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="font-bold text-slate-800 flex items-center">
                                        🔔 承認待ちの有給申請
                                    </h4>
                                    <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">{{ notifications.length }} 件</span>
                                </div>
                                <div class="space-y-3">
                                    <div v-for="notif in notifications" :key="notif.id" class="p-3 border border-slate-100 rounded-lg hover:bg-slate-50 transition relative group">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-purple-50 text-purple-700">
                                                有給申請
                                            </span>
                                            <span class="text-[11px] text-slate-400 font-mono">{{ notif.start_date }} 〜 {{ notif.end_date }}</span>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-900 mt-1.5">{{ notif.user ? notif.user.name : '不明' }} さんの有給取得</p>
                                        <p class="text-xs text-slate-500 mt-0.5 pr-12">理由: {{ notif.reason || '未入力' }}</p>
                                        
                                        <button @click="approveLeave(notif.id)" class="absolute right-3 top-1/2 -translate-y-1/2 bg-slate-900 text-white text-xs px-2.5 py-1 rounded shadow-sm hover:bg-indigo-600 transition opacity-0 group-hover:opacity-100">
                                            承認
                                        </button>
                                    </div>
                                    <div v-if="notifications.length === 0" class="text-center py-8 text-sm text-slate-400">
                                        承認待ちの申請はありません。
                                    </div>
                                </div>

                                <div class="mt-6 pt-6 border-t border-slate-100">
                                    <h5 class="text-xs font-bold text-slate-500 uppercase mb-3">自分の有給を申請する</h5>
                                    <form @submit.prevent="submitLeave" class="space-y-3">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input v-model="leaveForm.start_date" type="date" required class="block w-full rounded-lg border-slate-200 text-xs" />
                                            <input v-model="leaveForm.end_date" type="date" required class="block w-full rounded-lg border-slate-200 text-xs" />
                                        </div>
                                        <input v-model="leaveForm.reason" type="text" placeholder="取得理由（私用のため、など）" class="block w-full rounded-lg border-slate-200 text-xs" />
                                        <button type="submit" :disabled="leaveForm.processing" class="w-full bg-indigo-50 text-indigo-700 text-xs font-semibold py-2 rounded-lg hover:bg-indigo-100 transition">
                                            申請を送信
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>