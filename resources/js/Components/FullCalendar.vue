<script setup>
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
import moment from "jalali-moment";
import Modal from "@/Components/Modal.vue";
import DangerButton from "@/Components/DangerButton.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { router, useForm, usePage } from "@inertiajs/vue3";
import { toast } from "vue3-toastify";

const props = defineProps({
  events: {
    type: Object,
  },
  date: {
    type: String,
  },
});

const emit = defineEmits(["toggleMenu"]);
const role = usePage().props.auth.user.role;
const confirmingAppointment = ref(false);
const showInformationModal = ref(false);
const fullCalendar = ref();
const selected = ref();
const calendar = computed(() =>
  fullCalendar.value ? fullCalendar.value.getApi() : null
);
const form = useForm({
  date: "",
  startTime: "",
  endTime: "",
  dateId: "",
});

const processEvent = (info) => {
  switch (role) {
    case "student":
      if (info.event.extendedProps.status === "open") {
        setForm(info);
        confirmAppointment();
      } else if (info.event.extendedProps.status === "approved") {
        selected.value = info.event.extendedProps;
        showInformation();
      }
      break;
    case "teacher":
      selected.value = info.event.extendedProps;
      showInformation();
      break;
  }
};

function refetch() {
  calendar.value.removeAllEvents();
  calendar.value.addEventSource(props.events);
}

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
  locale: faLocale,
  direction: "rtl",
  timeZone: "Asia/Tehran",
  initialView: "dayGridMonth",
  firstDay: 6,
  events: props.events,
  editable: true,
  selectable: true,
  selectOverlap: false,
  dayMaxEvents: true,
  selectMirror: true,
  stickyHeaderDates: true,
  views: {
    dayGridMonth: {},
  },
  headerToolbar: {
    left: (role === "teacher" ? "menu" : "") + " refresh prev,next today",
    center: "title",
    right: "timeGridWeek,dayGridMonth,listMonth",
  },
  customButtons: {
    menu: {
      text: "منو",
      click: function () {
        emit("toggleMenu");
        setTimeout(() => calendar.value.updateSize(), 300);
      },
    },
    refresh: {
      text: "↻",
      hint: "بارگذاری مجدد",
      click: function () {
        router.reload({ only: ["events"] });
      },
    },
  },
  noEventsText: "هیچ رویدادی وجود ندارد",
  eventClick: function (info) {
    processEvent(info);
  },
  eventMouseEnter: function (info) {
    info.el.style.cursor = "pointer";
  },
  eventClassNames: function (arg) {
    switch (arg.event.extendedProps.status) {
      case "approved":
        return ["bg-lime-100", "hover:bg-lime-200"];
      case "pending":
        return ["bg-yellow-100"];
    }
  },
};

const setForm = (info) => {
  form.dateId = info.event.id;
  form.date = moment(info.event.start).utc().format("YYYY-MM-DD");
  form.startTime = moment(info.event.start).utc().format("HH:mm");
  form.endTime = moment(info.event.end).utc().format("HH:mm");
};

const confirmAppointment = () => {
  confirmingAppointment.value = true;
};

const createAppointment = () => {
  form.post(route("appointment.store"), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal();
      toast.success("با موفقیت ارسال شد.");
    },
    onError: (error) => {
      console.log(error);
      toast.error(error);
    },
  });
};

const closeModal = () => {
  confirmingAppointment.value = false;
  form.clearErrors();
};

const showInformation = () => {
  showInformationModal.value = true;
};

watch(
  () => props.date,
  (newDate) => calendar.value.changeView("timeGridDay", newDate)
);

watch(
  () => props.events,
  () => refetch()
);
</script>

<template>
  <FullCalendar
    class="w-full ms-5"
    :options="calendarOptions"
    ref="fullCalendar"
  >
  </FullCalendar>
  <Modal :show="confirmingAppointment" @close="closeModal">
    <div class="p-6">
      <h2 class="text-lg font-medium text-gray-900">
        آیا از انتخاب این زمان مطمئن هستید؟
      </h2>
      <div class="mt-6 flex justify-end">
        <DangerButton @click="closeModal">لفو</DangerButton>
        <PrimaryButton
          class="ms-3 !rounded-md"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="createAppointment()"
        >
          تایید
        </PrimaryButton>
      </div>
    </div>
  </Modal>

  <Modal :show="showInformationModal" @close="showInformationModal = false">
    <pre dir="ltr">{{ selected }}</pre>
    <div class="flex items-center m-6 gap-5">
      <div class="avatar">
        <div class="w-16 rounded-full">
          <img :src="selected.avatar" alt="avatar" />
        </div>
      </div>
      <p class="ml-3 text-neutral">{{ selected.name }}</p>
    </div>
  </Modal>
</template>
