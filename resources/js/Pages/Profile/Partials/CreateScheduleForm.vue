<script setup>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { PlusIcon } from "@heroicons/vue/24/solid";
import { CalendarIcon, ClockIcon, MinusIcon } from "@heroicons/vue/24/outline";
import { useForm } from "@inertiajs/vue3";
import moment from "jalali-moment";
import { computed, watch, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import SelectMenus from "@/Components/SelectMenus.vue";
import DatePicker from "@/Components/CustomDatePicker.vue";
import TimeInput from "@/Components/TimeInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { debounce } from "lodash";
import { toast } from "vue3-toastify";
import { datetime, RRule, RRuleSet, rrulestr } from "rrule";
import Editor from "@/Components/Editor.vue";

const props = defineProps({
  schedules: {
    type: Object,
  },
});
const confirmingScheduleCreation = ref(false);
const menu = ref(null);
const options = [
  {
    value: "none",
    name: "تکرار نمی‌شود",
  },
  {
    value: "weekly",
    name: "تکرار بصورت هفتگی",
  },
  {
    value: "custom",
    name: "سفارشی ...",
  },
];

const classes = {
  plusIcon:
    "h-8 w-8 p-1 border-2 border-blue-500 text-blue-500 rounded-full hover:bg-blue-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
  minusIcon:
    "h-8 w-8 p-1 border-2 border-red-500 text-red-500 rounded-full hover:bg-red-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
  copyIcon:
    "h-8 w-8 p-1 border-2 text-gray-500 rounded-full hover:bg-gray-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
  dateTable:
    "flex flex-col gap-4 md:flex-row justify-around items-center md:items-start border-r-2 border-r-blue-600 pt-1 mt-3",
};

const titles = {
  copyIcon: "کپی کردن زمان برای همه روزها",
  plusIcon: "افزودن زمان جدید",
  minusIcon: "حذف زمان",
};

const dateFormat = "YYYY-MM-DD";
const timeFormat = "HH:mm";
const datetimeFormat = "YYYY-MM-DD HH:mm";
const selectedDates = computed(() => {
  if (form.frequency === "none") {
    return form.dates.map((date) => date.date).filter((x) => x.trim());
  } else if (form.frequency === "custom") {
    return [form.custom.startDate, form.custom.endDate].filter((x) => x.trim());
  }
});
const days = {
  fa: ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنج‌شنبه", "جمعه"],
  en: ["SA", "SU", "MO", "TU", "WE", "TH", "FR"],
};

const errorMessage = {
  timeOverlap: "ساعت‌ها هم پوشانی دارند.",
  common: "ثبت اطلاعات با خطا مواجه شد.",
  pastDate: "تاریخ نمی‌تواند در گذشته باشد.",
  pastTime: "ساعت شروع نمی‌تواند قبل از امروز باشد.",
  timeRange: "زمان شروع باید قبل از زمان پایان باشد.",
  customDate: "تاریخ شروع باید قبل از تاریخ پایان باشد.",
  rrule: "این روز در این بازه وجود ندارد.",
  required: {
    date: "تاریخ را پر کنید.",
    start: "ساعت شروع نمی‌تواند خالی باشد.",
    end: "ساعت پایان نمی‌تواند خالی باشد.",
    title: "عنوان نمی‌تواند خالی باشد.",
  },
};

const today = {
  date: moment().format(dateFormat),
  time: moment().format(timeFormat),
  datetime: moment().format(datetimeFormat),
};

function addTime(time, value, type) {
  const timeMoment = moment(time, timeFormat);
  return timeMoment.add(value, type).format(timeFormat);
}

function removeTime(date, time) {
  const dateIndex = form.dates.indexOf(date);
  const timeIndex = date.times.indexOf(time);

  switch (form.frequency) {
    case "none":
      if (date.times.length === 1) {
        form.clearErrors(`dates.${date.id}.date`);
        form.dates.splice(dateIndex, 1);
      } else {
        date.times.splice(timeIndex, 1);
      }
      break;
    default:
      if (date.times.length === 1 && !date.disabled) {
        date.disabled = true;
        form.clearErrors(
          `dates.${date.id}.times.${time.id}.start`,
          `dates.${date.id}.times.${time.id}.end`
        );
        date.id = "";
      } else {
        date.times.splice(timeIndex, 1);
      }
      break;
  }
  const dateId = date.id ?? dateIndex;
  form.clearErrors(
    `dates.${dateId}.times.${time.id}.start`,
    `dates.${dateId}.times.${time.id}.end`
  );
}

function addTimeInput(date) {
  const last = date.times.at(-1);
  const start = last?.end || "";
  const end = start ? addTime(start, 1, "hour") : "";

  if (form.frequency !== "none") {
    const maxId = Math.max(
      ...form.dates.filter((date) => date.id !== "").map((date) => date.id)
    );
    date.id = date.id ?? maxId + 1;
    if (date.disabled) {
      date.disabled = false;
      return;
    }
  }
  date.times.push({
    id: last.id === date.times.length ? last.id + 1 : date.times.length,
    start,
    end,
  });
}

function addDateInput() {
  const last = form.dates.at(-1);
  form.dates.push({
    id: last.id === form.dates.length ? last.id + 1 : form.dates.length,
    date: "",
    times: [
      {
        id: 0,
        start: "",
        end: "",
      },
    ],
  });
}

function setDefault(formAttribute, frequency = "none") {
  switch (formAttribute) {
    case "dates":
      if (frequency === "none") {
        return [
          {
            id: 0,
            date: "",
            times: [{ id: 0, start: "", end: "" }],
          },
        ];
      } else {
        return days.en.flatMap((day, id) => ({
          id: id === 0 ? id : "",
          date: day,
          disabled: id !== 0,
          times: [{ id: 0, start: "", end: "" }],
        }));
      }
    case "custom":
      return [
        {
          interval: 1,
          startDate: today.date,
          noEndDate: true,
          endDate: "",
        },
      ][0];
  }
}

function initializeForm(frequency) {
  form.defaults("dates", setDefault("dates", frequency));
  form.defaults("custom", setDefault("custom", frequency));
  form.reset("dates", "custom");
  form.clearErrors();
}

const form = useForm({
  title: "",
  description: "",
  frequency: computed(() => (!menu.value ? "none" : menu.value.value)),
  dates: setDefault("dates"),
  custom: setDefault("custom"),
});

function realTimeValidation() {
  function validateDate(date) {
    const momentDate = moment(date.date, dateFormat);
    const nowDate = moment(today.date, dateFormat);
    if (momentDate.isBefore(nowDate)) {
      form.setError(`dates.${date.id}.date`, errorMessage.pastDate);
    } else {
      form.clearErrors(`dates.${date.id}.date`);
    }
  }

  function validateCustomDates(startDate, endDate) {
    if (startDate && endDate) {
      const startMoment = moment(startDate, dateFormat);
      const endMoment = moment(endDate, dateFormat);
      if (endMoment.isBefore(startMoment)) {
        form.setError(`custom.endDate`, errorMessage.customDate);
      } else {
        form.clearErrors(`custom.endDate`);
      }
    }
    if (form.custom.noEndDate && !endDate) {
      form.setError(`custom.endDate`, errorMessage.required.date);
    }
    if (form.custom.noEndDate) {
      form.clearErrors(`custom.endDate`);
    }
  }

  function validateTimeRange(dateId, time) {
    const startKey = `dates.${dateId}.times.${time.id}.start`;
    const endKey = `dates.${dateId}.times.${time.id}.end`;
    const startTime = moment(time.start, timeFormat);
    const endTime = moment(time.end, timeFormat);

    if (endTime.isBefore(startTime)) {
      form.setError(startKey, errorMessage.timeRange);
      form.setError(endKey, " ");
      return false; // Invalid range
    } else {
      form.clearErrors(startKey, endKey);
      return true; // Valid range
    }
  }

  function validateStartTime(date, time) {
    const startKey = `dates.${date.id}.times.${time.id}.start`;
    const momentDateTime = moment(`${date.date} ${time.start}`, datetimeFormat);
    const nowDateTime = moment(today.datetime, datetimeFormat);
    if (momentDateTime.isBefore(nowDateTime)) {
      form.setError(startKey, errorMessage.pastTime);
      return false;
    } else {
      form.clearErrors(startKey);
      return true;
    }
  }

  function checkOverlappingTimes(times, dateId) {
    const validTimes = times.filter((time) => validateTimeRange(time, dateId)); // Only include valid time ranges

    for (let i = 0; i < validTimes.length; i++) {
      for (let j = i + 1; j < validTimes.length; j++) {
        const timeA = validTimes[i];
        const timeB = validTimes[j];

        const overlap =
          timeA.start &&
          timeA.end &&
          timeB.start &&
          timeB.end &&
          timeA.start < timeB.end &&
          timeA.end > timeB.start;

        if (overlap) {
          form.setError(
            `dates.${dateId}.times.${timeA.id}.start`,
            errorMessage.timeOverlap
          );
          form.setError(`dates.${dateId}.times.${timeA.id}.end`, " ");
          form.setError(
            `dates.${dateId}.times.${timeB.id}.start`,
            errorMessage.timeOverlap
          );
          form.setError(`dates.${dateId}.times.${timeB.id}.end`, " ");
        }
      }
    }
  }

  form.dates.forEach((date) => {
    const times = date.times;

    // Date Validation
    if (form.frequency === "none" && date.date) {
      validateDate(date);
    }
    // Time Validation
    times.forEach((time) => {
      // Time Validation for None Frequency
      if (form.frequency === "none") {
        if (date.date && time.start) {
          // Checking if startTime is not in the past
          const firstValidation = validateStartTime(date, time);

          // checking if endTime is before startTime
          if (time.end && firstValidation) {
            validateTimeRange(date.id, time);
          }
        }
      }

      // Time Validation for Custom And Weekly Frequencies
      else {
        if (!date.disabled) {
          // checking if endTime is before startTime
          if (time.start && time.end) {
            validateTimeRange(date.id, time);
          }
        }
      }
    });

    // Check for overlapping times only with valid ranges
    checkOverlappingTimes(times, date.id);
  });

  if (form.frequency === "custom") {
    validateCustomDates(form.custom.startDate, form.custom.endDate);
  }
}

function updateId() {
  form.dates.forEach((date) => {
    form.frequency === "none" ? (date.id = form.dates.indexOf(date)) : "";
    date.times.forEach((time) => {
      time.id = date.times.indexOf(time);
    });
  });
}

watch(
  () => form.frequency,
  (newType) => {
    initializeForm(newType);
  }
);

watch(
  [() => form.dates, () => form.custom],
  debounce(() => {
    realTimeValidation();
  }, 300),
  {
    deep: true,
  }
);

function hasMoreActiveDates(date) {
  return form.dates.filter((d) => !d.disabled).length > 1 ||
    date.times.length > 1
    ? true
    : false;
}

function getWeekDay(day) {
  const daysMap = {
    SA: RRule.SA,
    SU: RRule.SU,
    MO: RRule.MO,
    TU: RRule.TU,
    WE: RRule.WE,
    TH: RRule.TH,
    FR: RRule.FR,
  };

  return daysMap[day] || "";
}

function validateWithSchedules(form, schedules) {
  function isTimeOverlap(formTime, scheduleTime) {
    const formStart = moment(formTime.start, "HH:mm");
    const formEnd = moment(formTime.end, "HH:mm");

    const scheduleStart = moment(scheduleTime.start, "HH:mm");
    const scheduleEnd = moment(scheduleTime.end, "HH:mm");

    return formStart.isBefore(scheduleEnd) && formEnd.isAfter(scheduleStart);
  }

  function generateOccurrencesForTimes(times, startDate, endDate) {
    return times.flatMap((time) => {
      if (
        !time.rrule ||
        typeof time.rrule !== "string" ||
        time.rrule.trim() === ""
      ) {
        console.warn("Invalid RRule string:", time.rrule);
        return [];
      }

      try {
        const rule = rrulestr(time.rrule);
        return rule.between(
          datetime(
            startDate.year(),
            startDate.month() + 1,
            startDate.date(),
            startDate.hour(),
            startDate.minute()
          ),
          datetime(endDate.year(), endDate.month() + 1, endDate.date())
        );
      } catch (error) {
        console.error("Error parsing RRule:", error);
        return [];
      }
    });
  }

  function isDateOverlap(
    formDate,
    scheduleDate,
    formFrequency,
    scheduleFrequency,
    scheduleCustom
  ) {
    if (formFrequency === scheduleFrequency && formFrequency !== "custom") {
      return formDate.date === scheduleDate.date;
    } else {
      let formStartDate = "";
      let formEndDate = "";
      let scheduleStartDate = "";
      let scheduleEndDate = "";
      let formOccurrences = [];
      let scheduleOccurrences = [];
      if (formFrequency === "weekly") {
        formStartDate = moment().utc();
        formEndDate = moment().utc().add(1, "year");
      } else if (formFrequency === "custom") {
        formStartDate = moment(form.custom.startDate, dateFormat).utc();
        formEndDate = form.custom.noEndDate
          ? moment().utc().add(1, "year")
          : moment(form.custom.endDate, dateFormat).utc();
      }

      if (scheduleFrequency === "weekly") {
        scheduleStartDate = moment().utc();
        scheduleEndDate = moment().utc().add(1, "year");
      } else if (scheduleFrequency === "custom") {
        scheduleStartDate = moment(scheduleCustom.startDate, dateFormat).utc();
        scheduleEndDate = scheduleCustom.endDate
          ? moment(scheduleCustom.endDate, dateFormat).utc()
          : moment().utc().add(1, "year");
      }

      if (formFrequency !== "none") {
        formOccurrences = generateOccurrencesForTimes(
          formDate.times,
          formStartDate,
          formEndDate
        );
      } else {
        formOccurrences.push(formDate.date);
      }
      if (scheduleFrequency !== "none") {
        scheduleOccurrences = generateOccurrencesForTimes(
          scheduleDate.times,
          scheduleStartDate,
          scheduleEndDate
        );
      } else {
        scheduleOccurrences.push(scheduleDate.date);
      }
      return formOccurrences.some((formOccurrence) =>
        scheduleOccurrences.some((scheduleOccurrence) =>
          moment(formOccurrence).isSame(scheduleOccurrence, "day")
        )
      );
    }
  }

  function hasOverlap(formDates, schedules) {
    for (const schedule of schedules) {
      for (const scheduleDate of schedule.dates) {
        for (const formDate of formDates) {
          if (
            isDateOverlap(
              formDate,
              scheduleDate,
              form.frequency,
              schedule.frequency,
              schedule.frequency === "custom" ? schedule.custom : null
            )
          ) {
            const timeConflict = formDate.times.some((formTime) =>
              scheduleDate.times.some((scheduleTime) =>
                isTimeOverlap(formTime, scheduleTime)
              )
            );
            if (timeConflict) {
              return schedule.title;
            }
          }
        }
      }
    }
    return null;
  }

  const formDates =
    form.frequency !== "none"
      ? form.dates.filter((date) => !date.disabled)
      : form.dates;
  const conflictTitle = hasOverlap(formDates, schedules);

  if (conflictTitle) {
    toast.error("خطای هم‌پوشانی با " + `"${conflictTitle}"`);
    form.setError("finalValidation", "این تاریخ از قبل ثبت شده است.");
    return false;
  }

  form.clearErrors("finalValidation");
  return true;
}

function createRRule() {
  if (form.frequency === "none") return;
  let validation = true;
  const formDates = form.dates.filter((date) => !date.disabled);
  const isEmpty = formDates.some((date) => {
    return date.times.some((time) => {
      return time.start === "" || time.end === "";
    });
  });
  if (isEmpty) return;

  const year = moment().year();
  const month = moment().month() + 1;
  const day = moment().date();

  if (form.frequency === "weekly") {
    formDates.forEach((date) => {
      date.times.forEach((time) => {
        const hour = moment(time.start, timeFormat).hour();
        const minute = moment(time.start, timeFormat).minute();
        const rruleSet = new RRuleSet();
        const rrule = new RRule({
          freq: RRule.WEEKLY,
          wkst: RRule.SA,
          byweekday: getWeekDay(date.date),
          dtstart: datetime(year, month, day, hour, minute),
        });
        rruleSet.rrule(rrule);
        time.rrule = rruleSet.toString();
      });
    });
  } else if (form.frequency === "custom") {
    const sYear = moment(form.custom.startDate, dateFormat).year();
    const sMonth = moment(form.custom.startDate, dateFormat).month() + 1;
    const sDay = moment(form.custom.startDate, dateFormat).date();

    if (!form.custom.noEndDate) {
      const eYear = moment(form.custom.endDate, dateFormat).year();
      const eMonth = moment(form.custom.endDate, dateFormat).month() + 1;
      const eDay = moment(form.custom.endDate, dateFormat).date();
      formDates.forEach((date) => {
        date.times.forEach((time) => {
          const hour = moment(time.start, timeFormat).hour();
          const minute = moment(time.start, timeFormat).minute();
          const rruleSet = new RRuleSet();
          const rrule = new RRule({
            freq: RRule.WEEKLY,
            wkst: RRule.SA,
            interval: form.custom.interval,
            byweekday: getWeekDay(date.date),
            dtstart: datetime(sYear, sMonth, sDay, hour, minute),
            until: datetime(eYear, eMonth, eDay, hour, minute),
          });
          rruleSet.rrule(rrule);
          const validated = validateRRule(date.id, time.id, rruleSet);
          if (validated) {
            time.rrule = rruleSet.toString();
          } else {
            validation = false;
          }
        });
      });
    } else {
      formDates.forEach((date) => {
        date.times.forEach((time) => {
          const hour = moment(time.start, timeFormat).hour();
          const minute = moment(time.start, timeFormat).minute();
          const rruleSet = new RRuleSet();
          const rrule = new RRule({
            freq: RRule.WEEKLY,
            wkst: RRule.SA,
            interval: form.custom.interval,
            byweekday: getWeekDay(date.date),
            dtstart: datetime(sYear, sMonth, sDay, hour, minute),
          });
          rruleSet.rrule(rrule);
          time.rrule = rruleSet.toString();
        });
      });
    }
  }

  return validation;
}

const confirmScheduleCreation = () => {
  confirmingScheduleCreation.value = true;
};

function validateRRule(dateId, timeId, rruleSet) {
  if (rruleSet.all().length === 0) {
    form.setError(`dates.${dateId}.times.${timeId}.rrule`, errorMessage.rrule);
    toast.error(errorMessage.rrule);
    return false;
  } else {
    form.clearErrors(`dates.${dateId}.times.${timeId}.rrule`);
    return true;
  }
}

const createSchedule = () => {
  const rruleCreation = createRRule();
  if (!validateWithSchedules(form, props.schedules)) return;
  if (form.frequency === "custom" && rruleCreation === false) return;
  form.transform((data) => ({
    ...data,
    dates:
      form.frequency !== "none"
        ? data.dates.filter((date) => !date.disabled)
        : data.dates,
    custom: data.custom.noEndDate
      ? { ...data.custom, endDate: "never" }
      : data.custom,
  }));
  form.post(route("schedule.store"), {
    preserveScroll: true,
    onSuccess: () => [closeModal(), toast.success("اطلاعات با موفقیت ثبت شد")],
    onError: () => [updateId(), toast.error(errorMessage.common)],
  });
};

const closeModal = () => {
  confirmingScheduleCreation.value = false;
  form.clearErrors();
  form.reset();
  menu.value = null;
};
</script>

<template>
  <section>
    <PrimaryButton
      class="w-full !rounded-md !bg-blue-600 hover:!bg-blue-800"
      @click="confirmScheduleCreation"
    >
      <span class="px-2 mx-auto">ایجاد زمان جدید</span>
      <PlusIcon class="size-7" />
    </PrimaryButton>
    <Modal :show="confirmingScheduleCreation" @close="closeModal">
      <div class="p-5">
        <div class="mb-3">
          <label class="text-neutral block mb-2">عنوان :</label>
          <TextInput
            id="title"
            v-model="form.title"
            @change="form.clearErrors('title')"
            type="text"
            class="block w-full text-black"
            :class="{ 'border-red-500': form.errors.title }"
            placeholder="افزودن عنوان"
          />
          <InputError :message="form.errors.title" class="mt-1" />
        </div>
        <div class="my-4">
          <label class="text-neutral block mb-2">توضیحات :</label>
          <Editor v-model="form.description" />
        </div>
        <div class="flex">
          <ClockIcon class="text-gray-800 size-5 me-3" />
          <div class="w-full">
            <p class="pb-2 text-sm text-black">در دسترس عموم</p>
            <p class="block text-xs text-gray-600">
              زمان‌هایی را که به‌طور منظم برای قرارها در دسترس هستید تنظیم کنید.
            </p>
          </div>
        </div>
        <SelectMenus v-model="menu" :options="options" />

        <div v-if="form.frequency === 'none'">
          <div
            v-for="(date, dateIndex) in form.dates"
            :key="dateIndex"
            :class="classes.dateTable"
          >
            <div>
              <DatePicker
                type="date"
                append-to="body"
                v-model="date.date"
                :disable="selectedDates"
              />
              <InputError
                :message="form.errors[`dates.${date.id}.date`]"
                class="mt-2"
              />
            </div>

            <!-- Time Slots -->
            <div class="flex flex-col w-1/2">
              <div v-for="(time, timeIndex) in date.times" :key="timeIndex">
                <div class="flex items-center">
                  <TimeInput
                    :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                    :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                    :start-classes="{
                      'bg-red-100':
                        form.errors[`dates.${date.id}.times.${time.id}.start`],
                    }"
                    :end-classes="{
                      'bg-red-100':
                        form.errors[`dates.${date.id}.times.${time.id}.end`],
                    }"
                    v-model:firstVModel="time.start"
                    v-model:secondVModel="time.end"
                  />
                  <div class="flex mr-1 ps-2">
                    <div :title="titles.plusIcon">
                      <PlusIcon
                        :class="classes.plusIcon"
                        class="ml-2"
                        v-if="time.id === date.times[0].id"
                        @click="addTimeInput(date)"
                      />
                    </div>
                    <div :title="titles.minusIcon">
                      <MinusIcon
                        :class="classes.minusIcon"
                        v-if="date.times.length > 1 || dateIndex !== 0"
                        @click="removeTime(date, time)"
                      />
                    </div>
                  </div>
                </div>
                <!-- ERRORS -->
                <div class="mt-1 mb-3">
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.start`]
                    "
                  />
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.end`]
                    "
                  />
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-center mt-5 md:justify-start ms-6">
            <SecondaryButton @click="addDateInput()" class="!rounded-full">
              <span class="px-8 mx-auto">افزودن تاریخ</span>
              <CalendarIcon class="w-5 h-5" />
            </SecondaryButton>
          </div>
        </div>

        <div v-else-if="form.frequency === 'weekly'">
          <div
            v-for="(date, dateIndex) in form.dates"
            :key="dateIndex"
            :class="classes.dateTable"
            class="!justify-between md:pr-10"
          >
            <span class="text-neutral">{{ days.fa[dateIndex] }}</span>
            <div class="flex flex-col w-3/4">
              <div v-for="(time, timeIndex) in date.times" :key="timeIndex">
                <div
                  class="flex items-center justify-around md:justify-between mb-10 md:mb-0"
                >
                  <TimeInput
                    v-if="!date.disabled"
                    :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                    :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                    :start-classes="{
                      'bg-red-100':
                        form.errors[`dates.${date.id}.times.${time.id}.start`],
                    }"
                    :end-classes="{
                      'bg-red-100':
                        form.errors[`dates.${date.id}.times.${time.id}.end`],
                    }"
                    v-model:firstVModel="time.start"
                    v-model:secondVModel="time.end"
                  />
                  <span class="ms-16" v-else>در دسترس نیست</span>
                  <div class="flex w-1/4 ms-2">
                    <div :title="titles.plusIcon">
                      <PlusIcon
                        :class="classes.plusIcon"
                        class="me-2"
                        v-if="time.id === date.times[0].id"
                        @click="addTimeInput(date)"
                      />
                    </div>
                    <div :title="titles.minusIcon">
                      <MinusIcon
                        :class="classes.minusIcon"
                        v-if="!date.disabled && hasMoreActiveDates(date)"
                        @click="removeTime(date, time)"
                      />
                    </div>
                  </div>
                </div>
                <!-- ERRORS -->
                <div class="mt-1 mb-3">
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.start`]
                    "
                  />
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.end`]
                    "
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="form.frequency === 'custom'">
          <div class="flex flex-col px-5 mt-2">
            <div>
              <span class="text-neutral">تکرار هر :</span>
              <TextInput
                class="w-16 px-2 mx-3 mt-1 text-neutral"
                :class="{
                  'border-red-500': form.errors[`custom.interval`],
                }"
                type="number"
                min="1"
                max="52"
                v-model="form.custom.interval"
                placeholder="چند"
              />
              <span class="text-neutral">هفته</span>
              <InputError
                class="mt-1"
                :message="form.errors[`custom.interval`]"
              />
            </div>

            <div class="flex items-start mt-3">
              <span class="me-3 text-neutral">شروع :</span>
              <div>
                <DatePicker
                  type="date"
                  append-to="body"
                  v-model="form.custom.startDate"
                  :disable="selectedDates"
                />
                <InputError
                  :message="form.errors[`custom.startDate`]"
                  class="mt-2 text-neutral"
                />
              </div>
            </div>
            <div class="flex items-start my-5">
              <span class="me-3 text-neutral">پایان :</span>
              <div>
                <div class="mb-3">
                  <Checkbox
                    id="noEndDate"
                    name="noEndDate"
                    v-model:checked="form.custom.noEndDate"
                  />
                  <InputLabel
                    class="inline text-neutral ms-2"
                    for="noEndDate"
                    value="هرگز"
                  />
                </div>
                <DatePicker
                  type="date"
                  append-to="body"
                  v-model="form.custom.endDate"
                  :disable="selectedDates"
                  :disabled="form.custom.noEndDate"
                  :class="{
                    'opacity-25': form.custom.noEndDate,
                  }"
                />
                <InputError
                  :message="form.errors[`custom.endDate`]"
                  class="mt-2"
                />
              </div>
            </div>
          </div>

          <div
            v-for="(date, dateIndex) in form.dates"
            :key="dateIndex"
            :class="classes.dateTable"
            class="!justify-between md:pr-10"
          >
            <span class="text-neutral">{{ days.fa[dateIndex] }}</span>
            <div class="flex flex-col w-3/4">
              <div v-for="(time, timeIndex) in date.times" :key="timeIndex">
                <div
                  class="flex items-center justify-around md:justify-between mb-10 md:mb-0"
                >
                  <TimeInput
                    v-if="!date.disabled"
                    :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                    :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                    :start-classes="{
                      'bg-red-100':
                        form.errors[
                          `dates.${date.id}.times.${time.id}.start`
                        ] ||
                        form.errors[`dates.${date.id}.times.${time.id}.rrule`],
                    }"
                    :end-classes="{
                      'bg-red-100':
                        form.errors[`dates.${date.id}.times.${time.id}.end`] ||
                        form.errors[`dates.${date.id}.times.${time.id}.rrule`],
                    }"
                    v-model:firstVModel="time.start"
                    v-model:secondVModel="time.end"
                  />
                  <span class="ms-16" v-else>در دسترس نیست</span>
                  <div class="flex w-1/4 ms-2">
                    <div :title="titles.plusIcon">
                      <PlusIcon
                        :class="classes.plusIcon"
                        class="ml-2"
                        v-if="time.id === date.times[0].id"
                        @click="addTimeInput(date)"
                      />
                    </div>
                    <div :title="titles.minusIcon">
                      <MinusIcon
                        :class="classes.minusIcon"
                        v-if="!date.disabled && hasMoreActiveDates(date)"
                        @click="removeTime(date, time)"
                      />
                    </div>
                  </div>
                </div>

                <!-- ERRORS -->
                <div class="mt-1 mb-3">
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.start`]
                    "
                  />
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.end`]
                    "
                  />
                  <InputError
                    :message="
                      form.errors[`dates.${date.id}.times.${time.id}.rrule`]
                    "
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-end mt-6">
          <SecondaryButton @click="closeModal"> لغو </SecondaryButton>
          <PrimaryButton
            class="ms-3 !rounded-md"
            :class="{ 'opacity-25': form.processing }"
            :disabled="form.processing"
            @click="createSchedule()"
          >
            ثبت
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </section>
</template>
