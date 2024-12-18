<script setup>
import { computed, ref, watch } from "vue";
import DatePicker from "vue3-persian-datetime-picker";
import hMoment from "moment-hijri";
import jMoment from "jalali-moment";

const timeFormat = "HH:mm";
const dateFormat = "YYYY-MM-DD";
const displayDateFormat = "ddd - jDD jMMMM jYYYY";
const updatedJalaaliYear = ref(jMoment().jYear());
const props = defineProps({
    type: {
        type: String,
    },
});

// تابع کمکی برای فرمت‌دهی بر اساس نوع
const getFormattedValue = (type, timeFormat, dateFormat) => {
    return type === "time"
        ? jMoment().format(timeFormat)
        : jMoment().format(dateFormat);
};

const displayFormat = computed(() =>
    props.type === "time" ? timeFormat : displayDateFormat
);
const format = computed(() =>
    props.type === "time" ? timeFormat : dateFormat
);
const initialValue = computed(() =>
    getFormattedValue(props.type, timeFormat, dateFormat)
);
const min = computed(() =>
    props.type === "time" ? "00:00" : jMoment().format(dateFormat)
);

// تعطیلات رسمی قمری
const hijriHolidays = [
    { month: 1, day: 9, offset: 0, description: "تاسوعای حسینی" }, // 1 in 1404 - 1410
    { month: 1, day: 10, offset: 0, description: "عاشورای حسینی" }, // 1 in 1404 - 1410
    {
        month: 2,
        day: 29,
        offset: 1,
        description: "شهادت امام رضا علیه السلام",
    }, // 1 in 1404 - 1410
    {
        month: 8,
        day: 15,
        offset: 0,
        description: "ولادت حضرت قائم عجل الله تعالی فرجه و جشن نیمه شعبان",
    }, // 1 in 1404 - 1410
    { month: 10, day: 1, offset: 0, description: "عید سعید فطر" }, // 1 in 1404 - 1410
    {
        month: 10,
        day: 2,
        offset: 0,
        description: "تعطیل به مناسبت عید سعید فطر",
    }, // 1 in 1404 - 1410
    {
        month: 10,
        day: 25,
        offset: 0,
        description: "شهادت امام جعفر صادق علیه السلام",
    }, // 1 in 1404 - 1410
    { month: 2, day: 20, offset: 1, description: "اربعین حسینی" },
    {
        month: 2,
        day: 28,
        offset: 1,
        description: "رحلت رسول اکرم؛شهادت امام حسن مجتبی علیه السلام",
    },
    {
        month: 3,
        day: 8,
        offset: 1,
        description: "شهادت امام حسن عسکری علیه السلام",
    },
    {
        month: 3,
        day: 17,
        offset: 1,
        description: "میلاد رسول اکرم و امام جعفر صادق علیه السلام",
    },
    {
        month: 6,
        day: 3,
        offset: 1,
        description: "شهادت حضرت فاطمه زهرا سلام الله علیها",
    },
    {
        month: 7,
        day: 13,
        offset: 1,
        description: "ولادت امام علی علیه السلام و روز پدر",
    },
    { month: 7, day: 27, offset: 1, description: "مبعث رسول اکرم (ص)" },
    { month: 9, day: 21, offset: 1, description: "شهادت حضرت علی علیه السلام" },
    { month: 12, day: 10, offset: 1, description: "عید سعید قربان" },
    { month: 12, day: 18, offset: 1, description: "عید سعید غدیر خم" },
];

// تعطیلات رسمی ایران
const iranianHolidays = [
    { month: 1, day: 1, description: "جشن نوروز\/جشن سال نو" },
    { month: 1, day: 2, description: "عیدنوروز" },
    { month: 1, day: 3, description: "عیدنوروز" },
    { month: 1, day: 4, description: "عیدنوروز" },
    { month: 1, day: 12, description: "روز جمهوری اسلامی" },
    { month: 1, day: 13, description: "جشن سیزده به در" },
    { month: 3, day: 14, description: "رحلت حضرت امام خمینی" },
    { month: 3, day: 15, description: "قیام ۱۵ خرداد" },
    { month: 11, day: 22, description: "پیروزی انقلاب اسلامی" },
    { month: 12, day: 29, description: "روز ملی شدن صنعت نفت ایران" },
    { month: 12, day: 30, description: "آخرین روز سال" },
];

const highlightedDates = ref([]);

// Update holiday dates based on the selected Jalaali year
function updateHolidayDates() {
    const gDate = jMoment(updatedJalaaliYear.value, "jYYYY")
        .startOf("jYear")
        .format("YYYY/MM/DD");
    const hDate = hMoment(gDate, "YYYY/MM/DD")
        .subtract(1, "day")
        .format("iYYYY/iMM/iDD");
    const updatedHijriHolidays = hijriHolidays.map((holiday) => {
        let date = {
            day: holiday.day,
            month: holiday.month,
            offset: holiday.offset,
            year: hMoment(hDate, "iYYYY/iMM/iDD").iYear(),
        };
        if (holiday.month < hMoment(hDate, "iYYYY/iMM/iDD").format("iM")) {
            date.year = hMoment(hDate, "iYYYY/iMM/iDD").add(1, "year").iYear();
        } else if (
            holiday.month == hMoment(hDate, "iYYYY/iMM/iDD").format("iM") &&
            holiday.day < hMoment(hDate, "iYYYY/iMM/iDD").format("iD")
        ) {
            date.year = hMoment(hDate, "iYYYY/iMM/iDD").add(1, "year").iYear();
        }
        const result = convertHijriToJalaali(date);
        const isLeapYear = jMoment(
            `${result.year}/${result.month}/${result.day}`,
            "jYYYY/jM/jD"
        ).jIsLeapYear();
        let finalResult = {
            day: result.day,
            month: result.month,
            year: date.year,
            description: holiday.description,
        };

        // Special handling for holidays within 1st to 11th Farvardin
        if (result.month === 1 && result.day < 11) {
            const secondOccurence = isLeapYear
                ? 19 + result.day
                : 18 + result.day;
            finalResult = {
                day: [result.day, secondOccurence],
                month: [result.month, 12],
                year: date.year,
                description: holiday.description,
            };
            return finalResult;
        }
        return finalResult;
    });

    highlightedDates.value = [...iranianHolidays, ...updatedHijriHolidays];
}

// Watch for changes in Jalaali year and update holidays accordingly
watch(updatedJalaaliYear, updateHolidayDates, { immediate: true });

// Convert Hijri to Jalaali date
function convertHijriToJalaali(hijriDate) {
    let { day, month, year, offset } = hijriDate;
    const endOfMonth = hMoment(`${year}/${month}/${day}`, "iYYYY/iM/iD")
        .endOf("iMonth")
        .format("iD");

    if (updatedJalaaliYear != 1403 && day != endOfMonth) {
        offset = 1;
    }
    if (month == 2 && day == 29 && endOfMonth == 30) {
        offset = 2; // شهادت امام رضا
    }

    const gregorianDate = hMoment(
        `${year}/${month}/${day}`,
        "iYYYY/iM/iD"
    ).format("YYYY/MM/DD");
    const jalaaliDate = jMoment(gregorianDate, "YYYY/MM/DD")
        .add(offset, "day")
        .format("jYYYY/jMM/jDD");

    return {
        day: parseInt(jMoment(jalaaliDate, "jYYYY/jMM/jDD").format("jD")),
        month: parseInt(jMoment(jalaaliDate, "jYYYY/jMM/jDD").format("jM")),
        year: parseInt(jMoment(jalaaliDate, "jYYYY/jMM/jDD").format("jYYYY")),
    };
}

// Memorize formattedDate to improve performance
const formattedDateCache = new Map();

function formattedDate(holiday) {
    const cacheKey = `${holiday.year}-${holiday.month}-${holiday.day}`;
    if (formattedDateCache.has(cacheKey)) {
        return formattedDateCache.get(cacheKey);
    }

    const days = [].concat(holiday.day);
    const months = [].concat(holiday.month);
    const maxLength = Math.max(days.length, months.length);
    const formattedDates = [];

    for (let i = 0; i < maxLength; i++) {
        const day = days[i % days.length];
        const month = months[i % months.length];
        formattedDates.push(`${month}/${day}`);
    }

    formattedDateCache.set(cacheKey, formattedDates);
    return formattedDates;
}

// Optimized highlight function with memorization
const highlight = (formatted, dateMoment) => {
    let attributes = {};

    const dateFormat = dateMoment.format("jM/jD");
    const matchingDescriptions = highlightedDates.value.flatMap((item) => {
        const dateFormats = formattedDate(item);
        if (dateFormats.includes(dateFormat)) {
            return item.description;
        }
        return [];
    });

    if (matchingDescriptions.length > 0) {
        attributes["title"] = matchingDescriptions.join(" - ");
        attributes["class"] = "text-red-600";
    }

    if (dateMoment.format("ddd") === "جمعه") {
        attributes["title"] = attributes["title"]
            ? `${attributes["title"]} - تعطیل`
            : "تعطیل";
        attributes["class"] = "text-red-600";
    }

    return attributes;
};

function onChange(date) {
    const newJalaaliYear = jMoment(date).format("jYYYY");
    if (updatedJalaaliYear.value !== newJalaaliYear) {
        updatedJalaaliYear.value = newJalaaliYear;
    }
}
</script>

<template>
    <date-picker
        :type="type"
        :format="format"
        :displayFormat="displayFormat"
        placeholder="تاریخ"
        :highlight="highlight"
        :convertNumbers="true"
        @year-change="onChange"
        @month-change="onChange"
        @next-month="onChange"
        @prev-month="onChange"
        :initial-value="initialValue"
        :min="min"
    >
        <!-- slot for "now-btn" -->
        <template #now-btn="{ vm, color, goToday, lang }">
            <button
                type="button"
                :style="{ color }"
                @click="
                    () => {
                        goToday();
                        onChange(jMoment());
                    }
                "
                v-text="'برو به امروز'"
            />
        </template>
    </date-picker>
</template>
