<script setup>
import StudentLayout from "@/Layouts/StudentLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
  <Head title="داشبورد" />
  <StudentLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-white">داشبورد</h2>
    </template>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            سلام {{ $page.props.auth.user.name }}
          </div>
        </div>
      </div>
    </div>
  </StudentLayout>
</template>
