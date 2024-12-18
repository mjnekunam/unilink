<script setup>
import "bootstrap-icons/font/bootstrap-icons.css";
import { Calendar } from "@fullcalendar/core";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import listPlugin from "@fullcalendar/list";
import interactionPlugin from "@fullcalendar/interaction";
import faLocale from "@fullcalendar/core/locales/fa";
import rrule from "@fullcalendar/rrule";
import momentPlugin from "@fullcalendar/moment";
import bootstrap5Plugin from "@fullcalendar/bootstrap5";
import { computed, ref, watch } from "vue";

const calendarRef = ref(null);
const calendar = computed(() =>
    calendarRef.value ? calendarRef.value.getApi() : null
);
const props = defineProps({
    events: {
        type: Object,
    },
    date: {
        type: String,
    },
});

const calendarOptions = {
    plugins: [
        timeGridPlugin,
        dayGridPlugin,
        interactionPlugin,
        listPlugin,
        rrule,
        bootstrap5Plugin,
        momentPlugin,
    ],
    initialView: "listMonth",
    locale: faLocale,
    direction: "rtl",
    firstDay: 6,
    events: props.events,
    headerToolbar: {
        left: "prev,next",
        center: "title",
        right: "dayGridMonth,timeGridWeek,listMonth",
    },
    timeZone: "Asia/Tehran",
    noEventsText: "هیچ رویدادی وجود ندارد",
    eventClick: function (info) {
        alert("View: " + info.event.title);
        info.el.style.borderColor = "red";
    },
};

watch(
    () => props.date,
    (newDate) => calendar.value.changeView("timeGridDay", newDate)
);
</script>

<template>
    <FullCalendar id="calendar" :options="calendarOptions" ref="calendarRef" />
</template>
