<script>
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid'
import moment from 'jalali-moment';

moment.locale('fa'); // تنظیم زبان فارسی

export default {
    components: {
        FullCalendar // make the <FullCalendar> tag available
    },
    data: function () {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin, interactionPlugin, timeGridPlugin],
                initialView: 'timeGridWeek',
                selectable: true,
                weekends: true,
                hiddenDays: [4, 5],
                buttonText: {
                    today: 'برو به امروز',
                    month: 'ماه',
                    week: 'هفته',
                    day: 'روز',
                    list: 'لیست'
                },
                events: [
                    { title: 'تایم آزاد', start: new Date() }
                ],
                locale: 'fa',
                direction: 'rtl',
                firstDay: 6,
            }
        }
    }
}
</script>

<template>
    <FullCalendar :options='calendarOptions' />
</template>
