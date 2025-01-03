<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
  <div
    class="flex flex-col items-center min-h-screen pt-6 bg-slate-700 sm:justify-center sm:pt-0"
    data-theme="light"
  >
    <div>
      <Link href="/">
        <ApplicationLogo class="w-20 h-20 text-white fill-current" />
      </Link>
    </div>

    <div
      class="w-full px-6 py-4 mt-6 overflow-hidden shadow-md bg-white sm:max-w-md sm:rounded-lg"
    >
      <slot />
    </div>
  </div>
</template>
