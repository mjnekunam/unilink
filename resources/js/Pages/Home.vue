<script setup>
import { Head, Link } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";

const props = defineProps({
  img: String,
});
</script>

<template>
  <Head title="Home" />
  <NavBar :user="$page.props.auth.user" class="relative z-10" />
  <div class="flex flex-col items-center justify-center min-h-screen">
    <img :src="img" class="opacity-10 absolute" />
    <section class="text-center p-8 shadow-lg">
      <p class="text-5xl font-bold leading-2">
        برنامه ریزی ساده ملاقات‌ها با یونی لینک
      </p>
      <p class="mt-8 text-lg">
        دارای ابزار قدرتمند و محیطی آسان و کاربرپسند برای ایجاد قرار ملاقات
      </p>
    </section>
    <Link as="button" :href="route('register')" class="btn btn-secondary z-10"
      >ثبت نام رایگان</Link
    >
  </div>
</template>
