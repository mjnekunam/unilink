<script setup>
import { computed } from "vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <GuestLayout>
        <Head title="تایید ایمیل" />

        <div class="mb-4 text-sm text-gray-600">
            از ثبت‌نام شما سپاسگزاریم! لطفاً پیش از شروع، ایمیل خود را با کلیک
            بر روی لینک ارسال‌شده به صندوق ورودی‌تان تأیید کنید. اگر ایمیل را
            دریافت نکرده‌اید، می‌توانیم آن را دوباره برای شما ارسال کنیم.
        </div>

        <div
            class="mb-4 text-sm font-medium text-green-600"
            v-if="verificationLinkSent"
        >
            یک لینک تأیید جدید به آدرس ایمیلی که در هنگام ثبت‌نام ارائه کرده‌اید
            ارسال شد.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    بازارسال لینک تایید
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >حروج</Link
                >
            </div>
        </form>
    </GuestLayout>
</template>
