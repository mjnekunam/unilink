<script setup>
import { CheckIcon, XMarkIcon } from "@heroicons/vue/24/outline";
import { useForm, usePage } from "@inertiajs/vue3";
import moment from "jalali-moment";
import { toast } from "vue3-toastify";
import { ref } from "vue";
import { computed } from "vue";
const props = defineProps({
  appointments: {
    type: Object,
  },

  img: {
    type: String,
  },
});

const otherRole = computed(() =>
  usePage().props.auth.user.role === "student" ? "teacher" : "student"
);
const classes = {
  checkIcon:
    "text-xl p-1 rounded-md border-2 text-emerald-500 border-emerald-500 hover:bg-green-500 hover:text-white transition ease-in-out duration-200",
  xmarkIcon:
    "text-xl p-1 rounded-md border-2 text-red-500 border-red-500 hover:bg-red-500 hover:text-white transition ease-in-out duration-200",
};

const selected = ref([]);
const displayFormat = "ddd - jDD jMMMM jYYYY";
function persianDigits(latinNumber) {
  if (typeof latinNumber !== "string") {
    latinNumber = String(latinNumber);
  }
  const persianDigits = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
  return latinNumber.replace(/\d/g, (digit) => persianDigits[digit]);
}

const status = (status) => {
  if (status === "approved") {
    return "تایید شده";
  } else if (status === "pending") {
    return "در انتظار";
  } else {
    return "رد شده";
  }
};
const form = useForm({
  id: [],
  status: "",
});

const approve = () => {
  form.id = selected.value;
  form.status = "approved";
  form.patch(route("appointment.update"), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("درخواست تایید شد.");
    },
    onFinish: () => form.reset(),
    onError: (error) => {
      toast.error(error["id.0"]);
    },
  });
};

const decline = () => {
  form.id = selected.value;
  form.delete(route("appointment.destroy"), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("درخواست رد شد.");
    },
    onFinish: () => form.reset(),
  });
};

const selectAll = computed({
  get() {
    return selected.value.length === props.appointments.length;
  },
  set(value) {
    if (value) {
      selected.value = props.appointments.map((appointment) => appointment.id);
    } else {
      selected.value = [];
    }
  },
});
</script>

<template>
  <section v-if="appointments.length !== 0">
    <div class="overflow-x-auto">
      <table class="table">
        <!-- head -->
        <thead class="text-right">
          <tr>
            <th>
              <label>
                <input
                  v-if="$page.props.auth.user.role === 'teacher'"
                  type="checkbox"
                  class="checkbox border border-slate-500"
                  v-model="selectAll"
                />
              </label>
            </th>
            <th>نام {{ otherRole === "teacher" ? "استاد" : "دانشجو" }}</th>
            <th>تاریخ</th>
            <th>ساعت</th>
            <th v-if="$page.props.auth.user.role === 'student'">
              وضعیت درخواست
            </th>
          </tr>
        </thead>
        <tbody class="text-right">
          <!-- row 1 -->
          <tr v-for="(appointment, index) in appointments" :key="index">
            <th :class="{ 'bg-error/50': form.errors[`id.${index}`] }">
              <label>
                <input
                  v-if="$page.props.auth.user.role === 'teacher'"
                  type="checkbox"
                  class="checkbox border border-slate-500"
                  :value="appointment.id"
                  v-model="selected"
                  :checked="false"
                />
              </label>
            </th>
            <td :class="{ 'bg-error/50': form.errors[`id.${index}`] }">
              <div class="flex items-center gap-3">
                <div class="avatar">
                  <div class="mask mask-squircle h-12 w-12">
                    <img
                      :src="appointment[otherRole]['avatar']"
                      alt="student_avatar"
                    />
                  </div>
                </div>
                <div>
                  <div class="font-bold">
                    {{ appointment[otherRole]["name"] }}
                  </div>
                </div>
              </div>
            </td>
            <td :class="{ 'bg-error/50': form.errors[`id.${index}`] }">
              <time :datetime="appointment.date">{{
                persianDigits(
                  moment(appointment.date, "YYYY-MM-DD")
                    .locale("fa")
                    .format(displayFormat)
                )
              }}</time>
            </td>
            <td :class="{ 'bg-error/50': form.errors[`id.${index}`] }">
              <time :datetime="appointment.endTime">{{
                persianDigits(appointment.endTime)
              }}</time>
              <span class="px-1">-</span>
              <time :datetime="appointment.startTime">{{
                persianDigits(appointment.startTime)
              }}</time>
            </td>
            <td v-if="$page.props.auth.user.role === 'student'">
              {{ status(appointment.status) }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div
      v-if="$page.props.auth.user.role === 'teacher'"
      dir="ltr"
      class="flex items-center gap-4 mt-5"
    >
      <button
        :class="[
          classes.checkIcon,
          { 'opacity-25': form.processing || selected.length === 0 },
        ]"
        :disabled="form.processing || selected.length === 0"
        @click="approve()"
      >
        <CheckIcon class="size-5" />
      </button>
      <button
        :class="[
          classes.xmarkIcon,
          { 'opacity-25': form.processing || selected.length === 0 },
        ]"
        :disabled="form.processing || selected.length === 0"
        @click="decline()"
      >
        <XMarkIcon class="size-5" />
      </button>
    </div>
  </section>
  <section v-else>
    <img class="size-40 mx-auto" :src="img" alt="empty" />
    <p class="text-center text-gray-800 mt-3">درخواستی یافت نشد</p>
  </section>
</template>

<style scoped>
th,
td {
  color: black;
}
</style>
