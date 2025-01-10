<script setup>
import TeacherLayout from "@/Layouts/TeacherLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
  <Head title="داشبورد" />
  <TeacherLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            سلام {{ $page.props.auth.user.name }}
          </div>
        </div>
      </div>
    </div>
  </TeacherLayout>
</template>
