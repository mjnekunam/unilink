<script setup>
import { ref } from "vue";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import FullCalendar from "@/Components/FullCalendar.vue";
import DatePicker from "@/Components/CustomDatePicker.vue";
import SelectMenus from "@/Components/SelectMenus.vue";
import { watch } from "vue";
const props = defineProps({
  teachers: Object,
  events: Object,
});
const date = ref(null);
const selected = ref(null);
const options = [
  {
    value: "0",
    avatar: "",
    name: "انتخاب استاد",
  },
  ...props.teachers.map((teacher) => ({
    value: teacher.id,
    avatar: teacher.avatar,
    name: teacher.name,
  })),
];
const urlParams = new URLSearchParams(window.location.search);
const teacherId = Number(urlParams.get("id"));
const selectedIndex =
  options.findIndex((option) => Number(option.value) === teacherId) || 0;
watch(selected, (teacher) => {
  router.reload({
    data: { id: teacher.value },
    preserveState: true,
    only: ["events"],
  });
});
</script>

<template>
  <Head title="تقویم" />
  <StudentLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-white">
        تقویم و زمان بندی
      </h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="justify-between p-6 text-gray-900 md:flex">
            <div class="flex flex-col gap-4">
              <div>
                <SelectMenus
                  :options="options"
                  :selected-index="selectedIndex"
                  v-model="selected"
                />
              </div>
              <DatePicker v-model="date" inline custom-input=".no-input" />
            </div>
            <FullCalendar :events="events" :date="date" />
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>
