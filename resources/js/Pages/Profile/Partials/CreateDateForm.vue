<script setup>
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { PlusIcon } from "@heroicons/vue/24/solid";
import {
    CalendarIcon,
    ClipboardIcon,
    ClockIcon,
    MinusIcon,
} from "@heroicons/vue/24/outline";
import { router, useForm } from "@inertiajs/vue3";
import moment from "jalali-moment";
import { computed, watch, ref } from "vue";
import InputError from "@/Components/InputError.vue";
import SelectMenus from "@/Components/SelectMenus.vue";
import DatePicker from "@/Components/CustomDatePicker.vue";
import TimeInput from "@/Components/TimeInput.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputLabel from "@/Components/InputLabel.vue";
import { debounce } from "lodash";
import { toast, ToastActions } from "vue3-toastify";
import { datetime, RRule, RRuleSet, rrulestr } from "rrule";

const props = defineProps({
    schedules: Object,
});

const schedules = props.schedules;
const confirmingDateCreation = ref(false);
const menu = ref(null);
const testRule = rrulestr(
    "DTSTART:20250104T233043Z\nRRULE:FREQ=WEEKLY;WKST=SA;INTERVAL=2;BYDAY=SA;UNTIL=20250319T232943Z"
);

const test2 = new RRule({
    freq: RRule.WEEKLY,
    wkst: RRule.SA,
    byweekday: RRule.MO,
    interval: 2,
    dtstart: moment().toDate(),
});
const classes = {
    plusIcon:
        "h-8 w-8 p-1 border-2 border-blue-500 text-blue-500 rounded-full hover:bg-blue-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
    minusIcon:
        "h-8 w-8 p-1 border-2 border-red-500 text-red-500 rounded-full hover:bg-red-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
    copyIcon:
        "h-8 w-8 p-1 border-2 text-gray-500 rounded-full hover:bg-gray-500 hover:cursor-pointer hover:text-white transition ease-in-out duration-200",
    dateTable:
        "flex flex-col md:flex-row justify-around items-center md:items-start border-r-2 border-r-blue-600 pt-1 mt-3",
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
        return [form.custom.startDate, form.custom.endDate].filter((x) =>
            x.trim()
        );
    }
});
const days = {
    fa: ["شنبه", "یکشنبه", "دوشنبه", "سه‌شنبه", "چهارشنبه", "پنج‌شنبه", "جمعه"],
    en: [
        "Saturday",
        "Sunday",
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
    ],
};

const errorMessage = {
    timeOverlap: "ساعت‌ها هم پوشانی دارند.",
    pastDate: "تاریخ نمی‌تواند در گذشته باشد.",
    pastTime: "ساعت شروع نمی‌تواند قبل از امروز باشد.",
    timeRange: "زمان شروع باید قبل از زمان پایان باشد.",
    customDate: "تاریخ شروع باید قبل از تاریخ پایان باشد.",
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
    const dateId = date.id ? date.id : dateIndex;
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
            ...form.dates
                .filter((date) => date.id !== "")
                .map((date) => date.id)
        );
        date.id = date.id ? date.id : maxId + 1;
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
            return frequency === "none"
                ? [
                      {
                          id: 0,
                          date: "",
                          times: [{ id: 0, start: "", end: "" }],
                      },
                  ]
                : days.en.flatMap((day, id) => ({
                      id: id === 0 ? id : "",
                      date: day,
                      disabled: id !== 0,
                      times: [{ id: 0, start: "", end: "" }],
                  }));
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
    form.reset("dates", "custom", "finalValidation");
    form.clearErrors();
}

const form = useForm({
    title: "",
    frequency: computed(() => (!menu.value ? "none" : menu.value.name)),
    dates: setDefault("dates"),
    custom: setDefault("custom"),
});

function validateForm() {
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
        const momentDateTime = moment(
            `${date.date} ${time.start}`,
            datetimeFormat
        );
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
        const validTimes = times.filter((time) =>
            validateTimeRange(time, dateId)
        ); // Only include valid time ranges

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

        if (form.frequency === "none" && date.date) {
            validateDate(date);
        }

        times.forEach((time) => {
            if (form.frequency === "none" && date.date && time.start) {
                validateStartTime(date, time);
            }
            if (time.start && time.end && validateStartTime(date, time)) {
                validateTimeRange(date.id, time);
            }
            // Ensure we validate time range here
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
    debounce(() => validateForm(), 200),
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
    let result = "";
    switch (day) {
        case "Saturday":
            result = RRule.SA;
            break;
        case "Sunday":
            result = RRule.SU;
            break;
        case "Monday":
            result = RRule.MO;
            break;
        case "Tuesday":
            result = RRule.TU;
            break;
        case "Wednesday":
            result = RRule.WE;
            break;
        case "Thursday":
            result = RRule.TH;
            break;
        case "Friday":
            result = RRule.FR;
            break;
    }
    return result;
}

function validateWithDB() {
    function isTimeOverlap(formTime, scheduleTime) {
        const formStart = moment(formTime.start, timeFormat);
        const formEnd = moment(formTime.end, timeFormat);

        const scheduleStart = moment(scheduleTime.start, timeFormat);
        const scheduleEnd = moment(scheduleTime.end, timeFormat);

        return (
            formStart.isBefore(scheduleEnd) && formEnd.isAfter(scheduleStart)
        );
    }

    function hasOverlap(formDates, schedules) {
        // حلقه برای بررسی هر قرار در آرایه schedules
        return schedules.some((schedule) =>
            schedule.dates.some((scheduleDate) =>
                formDates.some((formDate) => {
                    // check overlap only for "none" and "weekly" frequencies
                    if (
                        form.frequency === "custom" ||
                        schedule.frequency === "custom"
                    ) {
                        return false;
                    }
                    if (form.frequency === schedule.frequency) {
                        // if frequencies are same and dates aren't the same, there's no overlap
                        if (formDate.date !== scheduleDate.date) {
                            return false;
                        }
                    }
                    // if frequencies aren't the same, convert both dates to moment and then check for overlap
                    else {
                        const formMoment = moment(
                            formDate.date,
                            form.frequency === "none" ? "YYYY-MM-DD" : "dddd"
                        );
                        const scheduleMoment = moment(
                            scheduleDate.date,
                            schedule.frequency === "none"
                                ? "YYYY-MM-DD"
                                : "dddd"
                        );
                        // مقایسه روزها
                        if (!formMoment.isSame(scheduleMoment, "day")) {
                            return false;
                        }
                    }
                    // بررسی هم‌پوشانی زمان‌ها در یک تاریخ
                    return formDate.times.some((formTime) =>
                        scheduleDate.times.some((scheduleTime) =>
                            isTimeOverlap(formTime, scheduleTime)
                        )
                    );
                })
            )
        );
    }

    const formHasOverlap = hasOverlap(form.dates, schedules);
    if (formHasOverlap) {
        toast.error("این تاریخ از قبل ثبت شده است.");
        form.setError("finalValidation", "این تاریخ از قبل ثبت شده است.");
        return false;
    } else {
        form.clearErrors("finalValidation");
        return true;
    }
}

function createRRule() {
    if (form.frequency === "none") return;
    const formDates = form.dates.filter((date) => !date.disabled);
    const isEmpty = formDates.some((date) => {
        return date.times.some((time) => {
            return time.start === "" || time.end === "";
        });
    });
    if (isEmpty) return;

    const year = moment().year();
    const month = moment().month();
    const day = moment().day();

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
        const sMonth = moment(form.custom.startDate, dateFormat).month();
        const sDay = moment(form.custom.startDate, dateFormat).day();

        if (!form.custom.noEndDate) {
            const eYear = moment(form.custom.endDate, dateFormat).year();
            const eMonth = moment(form.custom.endDate, dateFormat).month();
            const eDay = moment(form.custom.endDate, dateFormat).day();
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
                    time.rrule = rruleSet.toString();
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
}

const confirmDateCreation = () => {
    confirmingDateCreation.value = true;
};

const createAppointment = () => {
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
    form.post(route("calendar.store"), {
        preserveScroll: false,
        onBefore: () => [createRRule(), validateWithDB()],
        onSuccess: () => [
            closeModal(),
            toast.success("اطلاعات با موفقیت ثبت شد"),
            router.visit(route("calendar")),
        ],
        onError: () => [updateId(), toast.error("ثبت اطلاعات با خطا مواجه شد")],
    });
};

const closeModal = () => {
    confirmingDateCreation.value = false;
    form.clearErrors();
    form.reset();
    menu.value = null;
};
</script>

<template>
    <section>
        <PrimaryButton
            class="w-full !rounded-md bg-sky-900"
            @click="confirmDateCreation"
        >
            <span class="px-2 mx-auto">ایجاد زمان جدید</span>
            <PlusIcon class="size-7" />
        </PrimaryButton>
        <Modal :show="confirmingDateCreation" @close="closeModal">
            <div class="p-5">
                <div class="mb-3">
                    <TextInput
                        id="title"
                        v-model="form.title"
                        @change="form.clearErrors('title')"
                        type="text"
                        class="block w-full"
                        :class="{ 'border-red-500': form.errors.title }"
                        placeholder="افزودن عنوان"
                    />
                    <InputError :message="form.errors.title" class="mt-1" />
                </div>
                <div class="flex">
                    <ClockIcon class="size-5 text-gray-800 me-3" />
                    <div class="w-full">
                        <p class="text-sm pb-2">دردسترس عموم</p>
                        <p class="block text-xs text-gray-600">
                            زمان‌هایی را که به‌طور منظم برای قرارها در دسترس
                            هستید تنظیم کنید.
                        </p>
                    </div>
                </div>
                <SelectMenus v-model="menu" />

                <div v-if="form.frequency === 'none'">
                    <div
                        v-for="(date, dateIndex) in form.dates"
                        :key="dateIndex"
                        :class="classes.dateTable"
                    >
                        <div class="mb-5 md:m-0">
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
                            <div
                                v-for="(time, timeIndex) in date.times"
                                :key="timeIndex"
                            >
                                <div class="flex items-center mb-2">
                                    <TimeInput
                                        :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                                        :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                                        :start-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.start`
                                                ],
                                        }"
                                        :end-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.end`
                                                ],
                                        }"
                                        v-model:firstVModel="time.start"
                                        v-model:secondVModel="time.end"
                                    />
                                    <div class="flex mr-1 ps-2">
                                        <div :title="titles.plusIcon">
                                            <PlusIcon
                                                :class="classes.plusIcon"
                                                class="ml-2"
                                                v-if="
                                                    time.id === date.times[0].id
                                                "
                                                @click="addTimeInput(date)"
                                            />
                                        </div>
                                        <div :title="titles.minusIcon">
                                            <MinusIcon
                                                :class="classes.minusIcon"
                                                v-if="
                                                    date.times.length > 1 ||
                                                    dateIndex !== 0
                                                "
                                                @click="removeTime(date, time)"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- ERRORS -->
                                <div class="mb-3 mt-1">
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.start`
                                            ]
                                        "
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.end`
                                            ]
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-center md:justify-start mt-5 ms-6">
                        <SecondaryButton
                            @click="addDateInput()"
                            class="!rounded-full"
                        >
                            <span class="mx-auto px-8">افزودن تاریخ</span>
                            <CalendarIcon class="h-5 w-5" />
                        </SecondaryButton>
                    </div>
                </div>

                <div v-else-if="form.frequency === 'weekly'">
                    <div
                        v-for="(date, dateIndex) in form.dates"
                        :key="dateIndex"
                        :class="classes.dateTable"
                        class="!justify-between px-5"
                    >
                        <span class="mb-5 md:m-0">{{
                            days.fa[dateIndex]
                        }}</span>
                        <div class="flex flex-col w-full md:w-2/3">
                            <div
                                v-for="(time, timeIndex) in date.times"
                                :key="timeIndex"
                            >
                                <div
                                    class="flex justify-center md:justify-between items-center"
                                >
                                    <TimeInput
                                        v-if="!date.disabled"
                                        :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                                        :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                                        :start-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.start`
                                                ],
                                        }"
                                        :end-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.end`
                                                ],
                                        }"
                                        v-model:firstVModel="time.start"
                                        v-model:secondVModel="time.end"
                                    />
                                    <span class="text-gray-500 ms-20" v-else
                                        >در دسترس نیست</span
                                    >
                                    <div class="flex w-1/3 mr-1 ps-2">
                                        <div :title="titles.plusIcon">
                                            <PlusIcon
                                                :class="classes.plusIcon"
                                                class="ml-2"
                                                v-if="
                                                    time.id === date.times[0].id
                                                "
                                                @click="addTimeInput(date)"
                                            />
                                        </div>
                                        <div :title="titles.minusIcon">
                                            <MinusIcon
                                                :class="classes.minusIcon"
                                                v-if="
                                                    !date.disabled &&
                                                    hasMoreActiveDates(date)
                                                "
                                                @click="removeTime(date, time)"
                                            />
                                        </div>
                                        <div :title="titles.copyIcon">
                                            <ClipboardIcon
                                                :class="classes.copyIcon"
                                                class="ms-2"
                                                v-if="time.start && time.end"
                                                @click="copyTime(date, time)"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <!-- ERRORS -->
                                <div class="mb-3 mt-1">
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.start`
                                            ]
                                        "
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.end`
                                            ]
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
                            <span>تکرار هر :</span>
                            <TextInput
                                class="mt-1 px-2 w-16 mx-3"
                                :class="{
                                    'border-red-500':
                                        form.errors[`custom.interval`],
                                }"
                                type="number"
                                min="1"
                                max="52"
                                v-model="form.custom.interval"
                                placeholder="چند"
                            />
                            <span>هفته</span>
                            <InputError
                                class="mt-1"
                                :message="form.errors[`custom.interval`]"
                            />
                        </div>

                        <div class="flex items-start mt-3">
                            <span class="me-3">شروع :</span>
                            <div>
                                <DatePicker
                                    type="date"
                                    append-to="body"
                                    v-model="form.custom.startDate"
                                    :disable="selectedDates"
                                />
                                <InputError
                                    :message="form.errors[`custom.startDate`]"
                                    class="mt-2"
                                />
                            </div>
                        </div>
                        <div class="flex items-start my-5">
                            <span class="me-3">پایان :</span>
                            <div>
                                <div class="mb-3">
                                    <Checkbox
                                        id="noEndDate"
                                        name="noEndDate"
                                        v-model:checked="form.custom.noEndDate"
                                    />
                                    <InputLabel
                                        class="inline text-black ms-2"
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
                        class="!justify-between px-5"
                    >
                        <span class="mb-5 md:m-0">{{
                            days.fa[dateIndex]
                        }}</span>
                        <div class="flex flex-col w-full md:w-2/3">
                            <div
                                v-for="(time, timeIndex) in date.times"
                                :key="timeIndex"
                            >
                                <div
                                    class="flex justify-center md:justify-between items-center"
                                >
                                    <TimeInput
                                        v-if="!date.disabled"
                                        :first-id="`dates_${dateIndex}_times_${timeIndex}_start`"
                                        :second-id="`dates_${dateIndex}_times_${timeIndex}_end`"
                                        :start-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.start`
                                                ],
                                        }"
                                        :end-classes="{
                                            'bg-red-100':
                                                form.errors[
                                                    `dates.${date.id}.times.${time.id}.end`
                                                ],
                                        }"
                                        v-model:firstVModel="time.start"
                                        v-model:secondVModel="time.end"
                                    />
                                    <span class="text-gray-500 ms-10" v-else
                                        >در دسترس نیست</span
                                    >
                                    <div class="flex w-1/3 mr-1 ps-2">
                                        <div :title="titles.plusIcon">
                                            <PlusIcon
                                                :class="classes.plusIcon"
                                                class="ml-2"
                                                v-if="
                                                    time.id === date.times[0].id
                                                "
                                                @click="addTimeInput(date)"
                                            />
                                        </div>
                                        <div :title="titles.minusIcon">
                                            <MinusIcon
                                                :class="classes.minusIcon"
                                                v-if="
                                                    !date.disabled &&
                                                    hasMoreActiveDates(date)
                                                "
                                                @click="removeTime(date, time)"
                                            />
                                        </div>
                                        <div :title="titles.copyIcon">
                                            <ClipboardIcon
                                                :class="classes.copyIcon"
                                                class="ms-2"
                                                v-if="time.start && time.end"
                                                @click="copyTime(date, time)"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <!-- ERRORS -->
                                <div class="mb-3 mt-1">
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.start`
                                            ]
                                        "
                                    />
                                    <InputError
                                        :message="
                                            form.errors[
                                                `dates.${date.id}.times.${time.id}.end`
                                            ]
                                        "
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal"> لغو </SecondaryButton>
                    <PrimaryButton
                        class="ms-3 bg-blue-600 !rounded-md"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="createAppointment()"
                    >
                        ثبت
                    </PrimaryButton>
                </div>
            </div>
            <pre dir="ltr">{{ form }}</pre>
        </Modal>
    </section>
</template>
