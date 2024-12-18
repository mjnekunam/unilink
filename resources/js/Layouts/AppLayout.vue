<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
    <main>
        <header>
            <Link :href="route('home')">Home</Link>
            <Link :href="route('login')">ورود</Link>
            <Link :href="route('register')">ثبت نام</Link>
        </header>
        <article>
            <slot />
        </article>
    </main>
</template>
