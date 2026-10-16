<!-- resources/js/Pages/Attendance/Summary.vue -->
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    month: String,
    rows: Array,
});

const changeMonth = (event) => {
    router.get(route('attendance.summary'), { month: event.target.value }, { preserveState: true });
};
</script>

<template>
    <Head title="勤怠集計" />
    <AuthenticatedLayout>
        <template #header>
            <div>
                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">労務管理</span>
                <h2 class="text-xl font-semibold text-slate-800 mt-1">月次勤怠集計</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <div class="flex items-end justify-between mb-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">対象月</label>
                            <input type="month" :value="month" @change="changeMonth" class="rounded-lg border-slate-200 text-sm" />
                        </div>
                        <p class="text-xs text-slate-500">実労働時間 = 退勤 − 出勤 − 休憩（打刻データから自動集計）</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-slate-100 text-sm">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 font-medium">
                                    <th class="border border-slate-100 px-4 py-3 text-left">社員名</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-32">出勤日数</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-40">実労働時間</th>
                                    <th class="border border-slate-100 px-4 py-3 text-right w-40">退勤打刻漏れ</th>
                                </tr>
                            </thead>
                            <tbody class="text-slate-700">
                                <tr v-for="row in rows" :key="row.user_id">
                                    <td class="border border-slate-100 px-4 py-3 font-semibold">{{ row.name }}</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-mono">{{ row.work_days }}日</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right font-mono font-bold">{{ row.total_hours }}h</td>
                                    <td class="border border-slate-100 px-4 py-3 text-right" :class="row.missing_clock_out > 0 ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ row.missing_clock_out }}件</td>
                                </tr>
                                <tr v-if="rows.length === 0">
                                    <td colspan="4" class="text-center py-8 text-slate-400">社員が登録されていません。</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
