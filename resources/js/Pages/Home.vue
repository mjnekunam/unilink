<script setup>
import { Head } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";

const props = defineProps({});
</script>

<template>
    <Head title="Home" />
    <NavBar :user="$page.props.auth.user" />
</template>
