<template>
    <div>

        <!-- PAGE TITLE -->
        <Head title="Management calendar" />

        <!-- SIDEBAR -->
        <SidebarMenu />

        <main class="lg:pl-80">
            <div class="px-4 sm:px-6 lg:px-8 mb-10">
                <div class="flex flex-col divide-y divide-line">

                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between space-y-5 xl:space-y-0 py-5 xl:h-24">

                        <!-- PAGE HEADER -->
                        <Header pageTitle="Management calendar" totalText="Total personal" totalCount="280" />

                        <!-- SELECT BOXES -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3.5 space-y-3.5 sm:space-y-0">
                            <button class="bg-brand hover:opacity-90 text-white uppercase text-sm font-medium rounded-md px-5 py-3" @click="addEvent">
                                Adaugă
                            </button>
                        </div>
                    </div>

                    <!-- MAIN CALENDAR -->
                    <div class="pt-8">
                        <CalendarInteraction :events="dayLimits" ref="calendarInteraction" @show-event="showEvent" />
                    </div>

                    <div class="card flex justify-content-center">
                        <Drawer v-model:visible="visible" header="Drawer" position="right" style="width:100%; max-width: 32rem">
                            <template #header>
                                <div class="flex align-items-center gap-2 mr-auto">
                                    <h2 class="font-semibold text-base text-brand uppercase">Adaugă limitare dată calendaristică</h2>
                                </div>
                            </template>

                            <div class="border-t border-line py-6">
                                <p class="text-base">Inserați un titlu și stabiliți intervalul de timp pentru a crea un eveniment nou.</p>

                                <form @submit.prevent="submit" class="grid sm:grid-cols-2 gap-x-3.5 gap-y-5 mt-5">
                                    <div class="sm:col-span-2 space-y-2">
                                        <InputLabel value="Nume eveniment" />
                                        <InputText v-model="form.eventName" type="text" class="w-full" :disabled="isEditMode" />
                                        <div v-if="$page.props.errors.eventName" class="text-red-500 !mt-1"> {{ $page.props.errors.eventName }} </div>
                                    </div>

                                    <div class="space-y-2">
                                        <InputLabel value="Dată începere eveniment" />
                                        <DatePicker v-model="form.dateStart" placeholder="Alege" class="w-full" dateFormat="dd.mm.yy" @update:modelValue="updateDatS()" :disabled="isEditMode"/>
                                        <div v-if="$page.props.errors.dateStart" class="text-red-500 !mt-1"> {{ $page.props.errors.dateStart }} </div>
                                    </div>

                                    <div class="space-y-2">
                                        <InputLabel value="Dată finalizare eveniment" />
                                        <DatePicker v-model="form.dateEnd" :minDate="form.dateStart" placeholder="Alege" class="w-full" dateFormat="dd.mm.yy" :disabled="isEditMode"/>
                                        <div v-if="$page.props.errors.dateEnd" class="text-red-500 !mt-1"> {{ $page.props.errors.dateEnd }} </div>
                                    </div>

                                    <div class="sm:col-span-2 flex items-center space-x-3.5 mt-1">
                                        <div v-if="!isEditMode">
                                            <div class="sm:col-span-2 flex items-center space-x-3.5 mt-1">
                                                <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                    Adaugă
                                                </PrimaryButton>

                                                <SecondaryButton @click="visible = false">
                                                    Anulează
                                                </SecondaryButton>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <PrimaryButton type="submit" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                                Șterge
                                            </PrimaryButton>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </Drawer>
                    </div>

                </div>
            </div>
        </main>
    </div>
</template>

<script setup>

import { Head, useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { useToast } from 'vue-toastification'

import Header from '@/Components/shared/c-page-header.vue'
import SidebarMenu from '@/Components/partials/c-sidebar-menu.vue'
import CalendarInteraction from '@/Components/shared/c-calendar-interaction.vue'
import Drawer from 'primevue/drawer'
import InputLabel from '@/Components/elements/InputLabel.vue'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import PrimaryButton from '@/Components/elements/PrimaryButton.vue'
import SecondaryButton from '@/Components/elements/SecondaryButton.vue'

defineProps({
    pageTitle: String,
    persons: Array,
    hourTypes: Array,
    dayLimits: Array,
})

// Get toast interface
const toast = useToast()

const isEditMode = ref(false)

const form = useForm({
    formAction: 'add',
    eventId: 0,
    eventName: null,
    dateStart: null,
    dateEnd: null,
})

const updateDatS = (value) => {
    // form.dateEnd = form.dateStart
}

const calendarInteraction = ref(null)

function submit () {
    const dateS = new Date(form.dateStart)
    const formattedDateS = dateS.toLocaleString('ro-RO', { timeZoneName: 'short' })

    const dateF = new Date(form.dateEnd)
    const formattedDateF = dateF.toLocaleString('ro-RO', { timeZoneName: 'short' })

    form
        .transform((data) => ({
            ...data,
            dateStart: formattedDateS,
            dateEnd: formattedDateF,
        }))
        .post('/adauga-eveniment', {
            preserveScroll: true,
            onSuccess: () => {
                router.reload({ only: ['dayLimits'] })
                calendarInteraction.value.refresh()
                form.reset('eventName')
                form.reset('dateStart')
                form.reset('dateEnd')
                visible.value = false

                if (form.formAction === 'add') {
                    // or with options
                    toast.success('Evenimentul a fost creat cu succes!', {
                        timeout: 3000,
                        position: 'bottom-right',
                    })
                }

                if (form.formAction === 'delete') {
                    // or with options
                    toast.success('Evenimentul a fost șters cu succes!', {
                        timeout: 3000,
                        position: 'bottom-right',
                    })
                }
            },
        })
}

const visible = ref(false)

const showEvent = (event) => {
    // console.log(event.clickInfo)
    isEditMode.value = true
    const eventData = event.clickInfo.event
    console.log(eventData.end)
    form.eventName = eventData.title
    form.dateStart = eventData.start
    form.dateEnd = eventData.end ? eventData.end : eventData.start
    form.eventId = eventData.id
    form.formAction = 'delete'
    visible.value = true
}

const addEvent = () => {
    form.reset('eventName')
    form.reset('dateStart')
    form.reset('dateEnd')
    isEditMode.value = false
    visible.value = true
    form.formAction = 'add'
}

</script>
