<script setup>
import { Head, Link } from "@inertiajs/vue3";
import NavBar from "@/Components/NavBar.vue";

const props = defineProps({});

// function handleImageError() {
//     document.getElementById('screenshot-container')?.classList.add('!hidden');
//     document.getElementById('docs-card')?.classList.add('!row-span-1');
//     document.getElementById('docs-card-content')?.classList.add('!flex-row');
//     document.getElementById('background')?.classList.add('!hidden');
// }
</script>

<template>
    <Head title="Home" />
    <NavBar></NavBar>
</template>
