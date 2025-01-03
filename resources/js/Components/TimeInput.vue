<script setup>
import { ClockIcon } from "@heroicons/vue/24/outline";
import VueTimepicker from "vue3-timepicker";

const props = defineProps([
  "firstId",
  "secondId",
  "firstVModel",
  "secondVModel",
  "startClasses",
  "endClasses",
]);

const emit = defineEmits([
  "update:firstVModel",
  "update:secondVModel",
  "change",
]);
function updateFirstVModel(value) {
  emit("update:firstVModel", value);
}

function updateSecondVModel(value) {
  emit("update:secondVModel", value);
}

const classes =
  "text-center text-black border-transparent outline-none bg-gray-50 rounded-md hover:bg-gray-200 transition ease-in-out duration-150";
</script>

<template>
  <div class="flex flex-col-reverse md:flex-row items-center" dir="ltr">
    <vue-timepicker
      :id="firstId"
      placeholder="پایان"
      hour-label="ساعت"
      minute-label="دقیقه"
      :model-value="secondVModel"
      :minute-interval="10"
      @update:model-value="updateSecondVModel"
      @change="$emit('change', $event)"
      hide-clear-button
      manual-input
      close-on-complete
      :input-class="[classes, startClasses]"
      input-width="7rem"
    >
      <!-- Input icon (image) -->
      <template v-slot:icon>
        <ClockIcon class="w-6 h-6 text-gray-700" />
      </template>
    </vue-timepicker>
    <span class="px-2 text-sm">-</span>
    <vue-timepicker
      :id="secondId"
      placeholder="شروع"
      hour-label="ساعت"
      minute-label="دقیقه"
      :model-value="firstVModel"
      :minute-interval="10"
      @update:model-value="updateFirstVModel"
      @change="$emit('change', $event)"
      hide-clear-button
      manual-input
      close-on-complete
      :input-class="[classes, endClasses]"
      input-width="7rem"
    >
      >
      <!-- Input icon (image) -->
      <template v-slot:icon>
        <ClockIcon class="w-6 h-6 text-gray-700" />
      </template>
    </vue-timepicker>
  </div>
</template>
