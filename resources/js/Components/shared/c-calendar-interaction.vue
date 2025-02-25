<template>
    <div>
        <FullCalendar :options="calendarOptions" ref="fullCalendar" />
    </div>
</template>

<script>

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import roLocale from '@fullcalendar/core/locales/ro'

export default {
    components: {
        FullCalendar,
    },
    props: ['events', 'calendarOptionsData'],
    emits: ['showEvent'],
    data () {
        return {
            calendarOptions: {
                plugins: [dayGridPlugin],
                initialView: 'dayGridMonth',
                locales: [roLocale],
                locale: 'ro',
                eventClick: this.handleEventClick,
            },
        }
    },
    mounted: function () {
        this.loadData()
    },
    methods: {
        refresh: function (event) {
            // console.log('refresh data')
            // console.log('Events: ')
            // console.log(this.events)
            this.reset()
            this.loadData()
        },
        reset: function () {
            const events = this.$refs.fullCalendar.calendar.getEvents()
            events.forEach(function (event, index) {
                event.remove()
            })
        },
        loadData: function () {
            this.$nextTick(function () {
                // Code that will run only after the
                // entire view has been rendered
                // console.log('Calendar loaded ...')
                // console.log('Events: ')
                // console.log(this.events)
                // console.log(this.$refs.fullCalendar.calendar)

                const _this = this
                this.events.forEach(function (event, index) {
                    // console.log(event)
                    // event.backgroundColor = '#f87171'
                    // event.borderColor = '#f87171'
                    console.log(event)
                    _this.$refs.fullCalendar.calendar.addEvent(event)
                })
            })
        },
        handleEventClick: function (clickInfo) {
            console.log('Event click')
            console.log(clickInfo)
            this.$emit('showEvent', { clickInfo })
        },
    },
}

</script>
