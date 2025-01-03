<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed, ref, watch, reactive } from "vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import { MinusIcon } from "@heroicons/vue/24/outline";
import moment from "jalali-moment";
import VueTableLite from "vue3-table-lite";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import { toast } from "vue3-toastify";

const props = defineProps({
  schedules: {
    type: Object,
  },
});
const displayFormat = "ddd - jDD jMMMM jYYYY";
const confirmingScheduleDeletion = ref(false);
const form = useForm({
  id: [],
});

const rows = reactive([]);

watch(
  () => props.schedules,
  (newSchedules) => {
    let id = 1;
    rows.length = 0; // پاک کردن قبلی‌ها قبل از اضافه کردن داده‌های جدید

    Object.values(newSchedules).forEach((schedule) => {
      const description = computed(() => {
        if (schedule.frequency === "custom") {
          return (
            "&nbsp;" +
            "<span class='text-sm leading-5 text-gray-600 bg-gray-100 rounded-md md:p-2'>" +
            "تکرار:&nbsp;" +
            "هر " +
            persianDigits(schedule.custom.interval) +
            "&nbsp;هفته" +
            "&nbsp;از:&nbsp;" +
            parseDate(schedule.custom.startDate) +
            (schedule.custom.endDate
              ? "&nbsp;تا:&nbsp;" + parseDate(schedule.custom.endDate)
              : "") +
            "</span>"
          );
        } else if (schedule.frequency === "weekly") {
          return "تکرار:&nbsp;" + "هر هفته";
        }
        return "";
      });

      schedule.dates.forEach((date) => {
        date.times.forEach((time) => {
          rows.push({
            id: persianDigits(id++),
            dateId: time.id,
            title: schedule.title + description.value,
            date: parseDate(date.date),
            time: persianDigits(time.start + "\t - \t" + time.end),
          });
        });
      });
    });
  },
  { immediate: true } // این به‌روزرسانی را بلافاصله پس از بارگذاری صفحه انجام می‌دهد
);

const confirmScheduleDeletion = () => {
  confirmingScheduleDeletion.value = true;
};

const deleteDate = () => {
  form.delete(route("schedule.destroy"), {
    preserveScroll: true,
    onSuccess: () => [
      closeModal(),
      toast.success("عملیات با موفقیت انجام شد."),
    ],
    onFinish: () => form.reset(),
  });
};

const closeModal = () => {
  confirmingScheduleDeletion.value = false;
  form.clearErrors();
  form.reset();
};

function persianDigits(latinNumber) {
  if (typeof latinNumber !== "string") {
    latinNumber = String(latinNumber);
  }
  const persianDigits = ["۰", "۱", "۲", "۳", "۴", "۵", "۶", "۷", "۸", "۹"];
  return latinNumber.replace(/\d/g, (digit) => persianDigits[digit]);
}

function parseDate(date) {
  const dateMoment = moment(date, "YYYY-MM-DD");
  return dateMoment.isValid()
    ? persianDigits(dateMoment.locale("fa").format(displayFormat))
    : getDay(date);
}
function getDay(day) {
  const daysMap = {
    SA: "شنبه",
    SU: "یکشنبه",
    MO: "دوشنبه",
    TU: "سه‌شنبه",
    WE: "چهارشنبه",
    TH: "پنج‌شنبه",
    FR: "جمعه",
  };

  return daysMap[day] || "";
}

const table = ref({
  rowClasses: (row) => {
    if (form.id.includes(row.dateId)) {
      return ["bg-red-50"];
    }
  },
  columns: [
    {
      label: "ردیف",
      field: "id",
      headerClasses: ["text-center"],
      columnClasses: ["text-center"],
      width: "1%",
    },
    {
      label: "",
      field: "dateId",
      headerClasses: ["hidden"],
      columnClasses: ["hidden"],
      width: "1%",
      isKey: true,
    },
    {
      label: "عنوان",
      field: "title",
      headerClasses: ["hidden"],
      columnClasses: ["hidden"],
      width: "1%",
    },
    {
      label: "تاریخ",
      field: "date",
      width: "15%",
    },
    {
      label: "ساعت",
      field: "time",
      width: "15%",
    },
  ],
  rows: rows,
  totalRecordCount: rows.length,
  groupingKey: "title",
  hasGroupToggle: true,
  groupingDisplay: function (key) {
    return "<p class='p-2 rounded-md ms-4'>عنوان: " + key + "</p>";
  },
  messages: {
    pagingInfo: "نمایش {0} تا {1} از {2} رکورد",
    pageSizeChangeLabel: "تعداد رکورد در هر صفحه:",
    gotoPageLabel: "شماره صفحه:",
    noDataAvailable: "داده‌ای موجود نیست",
  },
});

const updateCheckedRows = (rowsKey) => {
  form.id = rowsKey;
};
</script>

<template>
  <section>
    <PrimaryButton
      class="w-full !rounded-md !bg-red-600 hover:!bg-red-800"
      @click="confirmScheduleDeletion"
    >
      <span class="px-2 mx-auto">حذف زمان‌های ثبت شده</span>
      <MinusIcon class="size-7" />
    </PrimaryButton>
    <Modal :show="confirmingScheduleDeletion" @close="closeModal()">
      <VueTableLite
        :has-checkbox="true"
        :start-collapsed="false"
        :is-keep-collapsed="true"
        :grouping-key="table.groupingKey"
        :has-group-toggle="table.hasGroupToggle"
        :grouping-display="table.groupingDisplay"
        :rowClasses="table.rowClasses"
        :columns="table.columns"
        :rows="table.rows"
        :total="table.totalRecordCount"
        :messages="table.messages"
        :is-hide-paging="true"
        @is-finished="table.isLoading = false"
        @return-checked-rows="updateCheckedRows"
      />
      <div dir="ltr" class="m-5">
        <PrimaryButton
          class="!rounded-md !bg-red-600 hover:!bg-red-800 me-3"
          :class="{
            'opacity-25': form.processing || form.id.length === 0,
          }"
          :disabled="form.processing || form.id.length === 0"
          @click="deleteDate"
        >
          <span class="px-2 mx-auto">حذف</span>
        </PrimaryButton>
        <SecondaryButton class="!rounded-md" @click="closeModal">
          <span class="px-2 mx-auto">لفو</span>
        </SecondaryButton>
      </div>
    </Modal>
  </section>
</template>
