<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps({
    events: Array,
    projects: Array,
    users: Array, // 💡 Laravelから届く全ユーザー
});

const isModalOpen = ref(false);

const form = useForm({
    title: '',
    project_id: '',
    start: '',
    end: '',
    is_all_day: false,
    description: '',
    user_ids: [], // 💡 共有するユーザーIDを格納する配列
});

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    locale: 'ja',
    events: props.events,
    editable: true,
    selectable: true,
    
    select: (info) => {
        form.start = info.startStr.includes('T') ? info.startStr.slice(0, 16) : info.startStr + 'T09:00';
        form.end = info.endStr.includes('T') ? info.endStr.slice(0, 16) : info.startStr + 'T18:00';
        form.is_all_day = info.allDay;
        form.user_ids = []; // 初期化
        isModalOpen.value = true;
    },

    eventClick: (info) => {
        if (confirm(`予定「${info.event.title}」を削除しますか？`)) {
            router.delete(route('events.destroy', info.event.id));
        }
    }
});

const submit = () => {
    form.post(route('events.store'), {
        onSuccess: () => {
            isModalOpen.value = false;
            form.reset();
            calendarOptions.value.events = props.events;
        },
    });
};
</script>

<template>
    <Head title="共有カレンダー" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold text-slate-800">共有スケジュールカレンダー</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow-sm border border-slate-100 rounded-xl">
                    <FullCalendar :options="calendarOptions" />
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl border border-slate-100 w-full max-w-md p-6 overflow-hidden">
                <h3 class="text-lg font-bold text-slate-900 mb-4">新しい予定を追加</h3>
                
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">タイトル <span class="text-red-500">*</span></label>
                        <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500" placeholder="ミーティングなど" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">関連案件（任意）</label>
                        <select v-model="form.project_id" class="mt-1 block w-full rounded-lg border-slate-200 text-sm">
                            <option value="">案件なし</option>
                            <option v-for="project in projects" :key="project.id" :value="project.id">
                                {{ project.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">予定を共有するメンバー</label>
                        <div class="grid grid-cols-2 gap-2 max-h-24 overflow-y-auto border border-slate-100 p-2 rounded-lg bg-slate-50/50">
                            <div v-for="user in users" :key="user.id" class="flex items-center">
                                <input v-model="form.user_ids" :value="user.id" :id="'user-'+user.id" type="checkbox" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 text-xs" />
                                <label :for="'user-'+user.id" class="ml-2 text-xs text-slate-600 select-none">{{ user.name }}</label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">開始日時</label>
                            <input v-model="form.start" type="datetime-local" required class="mt-1 block w-full rounded-lg border-slate-200 text-sm" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">終了日時</label>
                            <input v-model="form.end" type="datetime-local" class="mt-1 block w-full rounded-lg border-slate-200 text-sm" />
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input v-model="form.is_all_day" id="is_all_day" type="checkbox" class="rounded border-slate-300 text-indigo-600" />
                        <label for="is_all_day" class="ml-2 text-sm text-slate-600">終日の予定にする</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700">概要メモ</label>
                        <textarea v-model="form.description" rows="2" class="mt-1 block w-full rounded-lg border-slate-200 text-sm" placeholder="アジェンダなど"></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-4 border-t border-slate-100">
                        <button @click="isModalOpen = false" type="button" class="bg-slate-100 text-slate-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-slate-200 transition">
                            キャンセル
                        </button>
                        <button type="submit" :disabled="form.processing" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-500 transition">
                            保存する
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.fc .fc-button-primary { background-color: #4f46e5 !important; border-color: #4f46e5 !important; }
.fc .fc-button-primary:hover { background-color: #4338ca !important; }
.fc .fc-toolbar-title { font-size: 1.25rem !important; font-weight: 700 !important; color: #1e293b !important; }
</style>