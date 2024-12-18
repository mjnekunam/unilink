<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import FullCalendar from "@/Components/FullCalendar.vue";
import CreateDateForm from "./Profile/Partials/CreateDateForm.vue";
import DatePicker from "@/Components/CustomDatePicker.vue";
import { ref } from "vue";

defineProps({
    events: {
        type: Object,
    },
    schedules: {
        type: Object,
    },
});
const date = ref(null);
</script>

<template>
    <Head title="تقویم" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                تقویم و زمان بندی
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="md:flex justify-between p-6 text-gray-900">
                        <div class="flex flex-col">
                            <CreateDateForm
                                :schedules="schedules"
                                class="max-w-xl mb-5"
                            />
                            <DatePicker
                                v-model="date"
                                inline
                                custom-input=".no-input"
                            />
                        </div>
                        <FullCalendar
                            :events="events"
                            :date="date"
                            class="w-full ms-5"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
