<script setup>
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900 mb-2">
                حذف حساب کاربری
            </h2>

            <p class="mt-1 text-sm leading-6 text-gray-600">
                پس از حذف حساب شما، تمام منابع و داده های آن برای همیشه حذف می
                شوند. قبل از حذف حساب خود، لطفاً هر گونه داده یا اطلاعاتی را که
                می خواهید حفظ کنید دانلود کنید.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion"
            >حذف حساب کاربری</DangerButton
        >

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    آیا از حذف حساب کاربری خود مطمئن هستید؟
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    پس از حذف حساب شما، تمام منابع و داده های آن برای همیشه حذف
                    می شوند. لطفاً برای حذف حساب کاربری رمزعبور خود را وارد
                    کنید.
                </p>

                <div class="mt-6">
                    <InputLabel
                        for="password"
                        value="Password"
                        class="sr-only"
                    />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="Password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> لغو </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        تایید
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
