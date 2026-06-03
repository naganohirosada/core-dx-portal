<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, Link } from '@inertiajs/vue3';

const props = defineProps({
    project: Object,
    tasks: Array,
});

// タスク追加フォーム
const form = useForm({
    title: '',
    due_date: '',
});

const submit = () => {
    form.post(route('projects.tasks.store', props.project.id), {
        onSuccess: () => form.reset('title'),
    });
};

// 💡 工数入力モーダル用のステート
const isLogModalOpen = ref(false);
const selectedTask = ref(null);

const logForm = useForm({
    hours: '',
    date: new Date().toISOString().slice(0, 10), // 初期値は今日の日付
});

// 💡 タスクがクリックされたらモーダルを開く
const openLogModal = (task) => {
    selectedTask.value = task;
    logForm.hours = '';
    isLogModalOpen.value = true;
};

// 工数の送信処理
const submitWorkLog = () => {
    logForm.post(route('tasks.work-logs.store', selectedTask.value.id), {
        onSuccess: () => {
            isLogModalOpen.value = false;
            alert('工数を記録しました！');
        }
    });
};

// ドラッグ＆ドロップ用ステート
const draggingTaskId = ref(null);
const onDragStart = (taskId) => { draggingTaskId.value = taskId; };
const onDrop = (status) => {
    if (!draggingTaskId.value) return;
    router.patch(route('tasks.status.update', draggingTaskId.value), {
        status: status
    }, { preserveScroll: true, onSuccess: () => { draggingTaskId.value = null; } });
};

const todoTasks = computed(() => props.tasks.filter(t => t.status === 'todo'));
const doingTasks = computed(() => props.tasks.filter(t => t.status === 'doing'));
const doneTasks = computed(() => props.tasks.filter(t => t.status === 'done'));
</script>

<template>
    <Head :title="project.name + ' - タスクボード'" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded-md">案件タスク管理</span>
                    <h2 class="text-xl font-semibold text-slate-800 mt-1">{{ project.name }}</h2>
                </div>
                <Link :href="route('projects.index')" class="text-sm text-slate-500 hover:text-slate-800">← 案件一覧に戻る</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                
                <div class="bg-white p-4 shadow-sm border border-slate-100 rounded-xl">
                    <form @submit.prevent="submit" class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-slate-500 mb-1">新しいタスク</label>
                            <input v-model="form.title" type="text" required class="block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="要件定義書の作成など" />
                        </div>
                        <div class="w-full md:w-48">
                            <label class="block text-xs font-medium text-slate-500 mb-1">期日（任意）</label>
                            <input v-model="form.due_date" type="date" class="block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>
                        <button type="submit" class="bg-slate-900 text-white px-5 py-2 rounded-lg text-sm font-medium shadow-sm hover:bg-slate-800 transition whitespace-nowrap h-[42px]">
                            ＋ 追加
                        </button>
                    </form>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <div @dragover.prevent @drop="onDrop('todo')" class="bg-slate-50/80 border border-slate-100 p-4 rounded-xl min-h-[500px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-700 flex items-center"><span class="w-2 h-2 rounded-full bg-slate-400 mr-2"></span> 未着手</h3>
                            <span class="text-xs font-bold text-slate-400 bg-slate-200/60 px-2 py-0.5 rounded-full">{{ todoTasks.length }}</span>
                        </div>
                        <div class="space-y-3">
                            <div v-for="task in todoTasks" :key="task.id" draggable="true" @dragstart="onDragStart(task.id)" @click="openLogModal(task)" class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 cursor-grab active:cursor-grabbing hover:border-indigo-300 transition group relative">
                                <p class="text-sm font-medium text-slate-800 group-hover:text-indigo-600 transition">{{ task.title }}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span v-if="task.due_date" class="text-[10px] text-slate-400">⏳ {{ task.due_date }}</span>
                                    <span class="text-[9px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">⏱️ 工数入力</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div @dragover.prevent @drop="onDrop('doing')" class="bg-slate-50/80 border border-slate-100 p-4 rounded-xl min-h-[500px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-700 flex items-center"><span class="w-2 h-2 rounded-full bg-amber-400 mr-2"></span> 進行中</h3>
                            <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">{{ doingTasks.length }}</span>
                        </div>
                        <div class="space-y-3">
                            <div v-for="task in doingTasks" :key="task.id" draggable="true" @dragstart="onDragStart(task.id)" @click="openLogModal(task)" class="bg-white p-4 rounded-lg shadow-sm border border-slate-100 cursor-grab active:cursor-grabbing hover:border-indigo-300 transition group relative">
                                <p class="text-sm font-medium text-slate-800 group-hover:text-indigo-600 transition">{{ task.title }}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span v-if="task.due_date" class="text-[10px] text-slate-400">⏳ {{ task.due_date }}</span>
                                    <span class="text-[9px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">⏱️ 工数入力</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div @dragover.prevent @drop="onDrop('done')" class="bg-slate-50/80 border border-slate-100 p-4 rounded-xl min-h-[500px]">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-bold text-slate-700 flex items-center"><span class="w-2 h-2 rounded-full bg-emerald-400 mr-2"></span> 完了</h3>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">{{ doneTasks.length }}</span>
                        </div>
                        <div class="space-y-3">
                            <div v-for="task in doneTasks" :key="task.id" draggable="true" @dragstart="onDragStart(task.id)" @click="openLogModal(task)" class="bg-white p-4 rounded-lg shadow-sm border border-emerald-100 bg-emerald-50/10 cursor-grab active:cursor-grabbing opacity-80 hover:border-indigo-300 transition group relative">
                                <p class="text-sm font-medium text-slate-500 line-through group-hover:text-indigo-600 transition">{{ task.title }}</p>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-[10px] text-emerald-600">✅ 完了</span>
                                    <span class="text-[9px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition">⏱️ 工数入力</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div v-if="isLogModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl border border-slate-100 w-full max-w-sm p-6 overflow-hidden transform transition-all">
                <div class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md inline-block mb-2">工数実績入力</div>
                <h3 class="text-base font-bold text-slate-900 mb-4 line-clamp-2">{{ selectedTask?.title }}</h3>
                
                <form @submit.prevent="submitWorkLog" class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">作業日</label>
                        <input v-model="logForm.date" type="date" required class="block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">実働時間 (時間)</label>
                        <input v-model="logForm.hours" type="number" step="0.5" min="0.1" max="24" required class="block w-full rounded-lg border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="例: 1.5 や 4" />
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                        <button @click="isLogModalOpen = false" type="button" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-xs font-medium hover:bg-slate-200 transition">
                            閉じる
                        </button>
                        <button type="submit" :disabled="logForm.processing" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-slate-800 transition disabled:opacity-50">
                            工数を記録する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>