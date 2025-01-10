<script setup>
import { ref } from "vue";
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import { Head } from "@inertiajs/vue3";
import FullCalendar from "@/Components/FullCalendar.vue";
import DatePicker from "@/Components/CustomDatePicker.vue";
import CreateScheduleForm from "@/Pages/Profile/Partials/CreateScheduleForm.vue";
import DeleteScheduleForm from "@/Pages/Profile/Partials/DeleteScheduleForm.vue";

defineProps({
  schedules: {
    type: Object,
  },
  events: {
    type: Object,
  },
});

const showMenu = ref(false);
const toggleMenu = () => {
  showMenu.value = !showMenu.value;
};
const date = ref(null);
</script>

<template>
  <Head title="تقویم" />
  <TeacherLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
          <div class="justify-between p-6 text-gray-900 md:flex">
            <Transition name="fade" :duration="200">
              <div v-if="showMenu" class="flex flex-col gap-5">
                <CreateScheduleForm :schedules="schedules" class="w-full" />
                <DeleteScheduleForm :schedules="schedules" class="w-full" />
                <DatePicker v-model="date" inline custom-input=".no-input" />
              </div>
            </Transition>
            <FullCalendar
              :events="events"
              :date="date"
              @toggle-menu="toggleMenu"
            />
          </div>
        </div>
      </div>
    </div>
  </TeacherLayout>
</template>
