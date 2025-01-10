<script setup>
import AppointmentTable from "@/Components/AppointmentTable.vue";
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
  appointments: {
    type: Object,
  },
  img: {
    type: String,
  },
});
</script>

<template>
  <Head title="درخواست‌ها" />
  <StudentLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <AppointmentTable :appointments="appointments" :img="img" />
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>
