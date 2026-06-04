<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    project: Object
});

const statusLabel = (status) => {
    const labels = { todo: '未着手', doing: '進行中', done: '完了' };
    return labels[status] || status;
};
</script>

<template>
    <Head :title="project.name + ' - 案件詳細'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Project Cockpit</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-1">{{ project.name }} の詳細状況</h2>
                </div>
                <Link :href="route('projects.index')" class="text-sm text-slate-500 hover:text-slate-800">← 案件一覧に戻る</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-1 h-fit space-y-5">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase">Client</h3>
                        <p class="font-bold text-slate-900 text-base mt-0.5">{{ project.company?.name }}</p>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase">Budget</h3>
                        <p class="font-mono font-bold text-slate-900 text-lg mt-0.5">￥{{ project.budget?.toLocaleString() || '-' }}</p>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase">Period</h3>
                        <p class="text-sm text-slate-700 font-medium mt-0.5">{{ project.start_date || '未定' }} 〜 {{ project.end_date || '未定' }}</p>
                    </div>
                    <div class="border-t border-slate-100 pt-4">
                        <Link :href="route('projects.tasks.board', project.id)" class="w-full block text-center bg-slate-900 text-white font-bold py-2.5 rounded-lg text-xs hover:bg-indigo-600 transition shadow-sm">
                            📋 ドラッグ＆ドロップ カンバンボードを開く
                        </Link>
                    </div>
                </div>

                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl lg:col-span-2">
                    <h3 class="text-sm font-bold text-slate-900 mb-4">現在のタスクアサイン ＆ 進捗状況</h3>
                    <div class="divide-y divide-slate-100">
                        <div v-for="task in project.tasks" :key="task.id" class="py-3.5 flex justify-between items-center group">
                            <div>
                                <p class="text-sm font-semibold text-slate-800" :class="{'line-through text-slate-400': task.status === 'done'}">{{ task.title }}</p>
                                <p v-if="task.due_date" class="text-[11px] text-slate-400 mt-1">⏳ 期日: {{ task.due_date }}</p>
                            </div>
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md" :class="{
                                'bg-slate-100 text-slate-600': task.status === 'todo',
                                'bg-amber-50 text-amber-700': task.status === 'doing',
                                'bg-emerald-50 text-emerald-700': task.status === 'done',
                            }">
                                {{ statusLabel(task.status) }}
                            </span>
                        </div>
                        <div v-if="project.tasks?.length === 0" class="text-center py-12 text-sm text-slate-400">
                            現在、この案件に登録されているタスクはありません。
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>