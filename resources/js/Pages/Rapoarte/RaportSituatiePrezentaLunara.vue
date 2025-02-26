<template>
    <div>

        <!-- Page Title -->
        <Head title="Situație prezență lunară" />

        <!-- Sidebar -->
        <SidebarMenu class="print:hidden" />

        <main class="lg:pl-80 print:hidden">
            <div class="px-4 sm:px-6 lg:px-8 mb-10">
                <div class="flex flex-col divide-y divide-line">

                    <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between space-y-5 xl:space-y-0 py-5 xl:h-24">

                        <!-- Page Header -->
                        <Header pageTitle="Raport situație prezență lunară" customText="Vezi mai jos situația prezenței lunare la program a personalului"/>

                        <!-- SELECT BOXES -->
                        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-3.5 space-y-3.5 sm:space-y-0">
                            <!-- Compartment Select -->
                            <Select
                                v-model="selectedBusinessUnitGroup"
                                :options="businessUnitGroups"
                                filter
                                optionLabel="name"
                                placeholder="Selectează structura"
                                class="w-full md:w-60"
                            >
                                <template #value="slotProps">
                                    <div v-if="slotProps.value" class="flex align-items-center">
                                        <div>{{ slotProps.value.name }}</div>
                                    </div>
                                    <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span>
                                </template>
                                <template #option="slotProps">
                                    <div class="flex align-items-center">
                                        <div>{{ slotProps.option.name }}</div>
                                    </div>
                                </template>
                            </Select>

                            <!-- Month Picker -->
                            <DatePicker
                                v-model="date"
                                view="month"
                                dateFormat="mm/yy"
                                :showIcon="true"
                                placeholder="Selectează luna"
                                class="w-full md:w-44"

                            />

                            <!-- Print Button -->
                            <button
                                class="bg-brand hover:opacity-90 text-white uppercase text-sm font-medium rounded-md px-5 py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="!selectedBusinessUnitGroup || !reportData.people?.length"
                                @click="printTable">
                                Printează
                            </button>

                            <!-- Buton resetare modificari edits -->
                            <button v-if="hasEdits"
                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 uppercase text-sm font-medium rounded-md px-5 py-3 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="!hasEdits"
                                    @click="clearAllEdits">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                            </button>

                        </div>
                    </div>

                    <!-- Add No Filter Selected State -->
                    <div v-if="!selectedBusinessUnitGroup" class="flex justify-center items-center h-[calc(100vh-12rem)]">
                        <div class="text-center">
                            <img :src="'/images/select-structure.png'" class="w-64 mx-auto">
                            <div class="flex flex-col mt-3">
                                <h3 class="text-lg font-medium text-brand">Selectează o structură</h3>
                                <p>Pentru a vizualiza datele, te rugăm să selectezi o structură.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div v-else-if="loading" class="flex justify-center items-center h-[calc(100vh-12rem)]">
                        <div class="text-center">
                            <i class="pi pi-spin pi-spinner text-5xl text-brand"></i>
                            <div class="mt-2">Se încarcă datele...</div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else-if="!reportData.people?.length" class="flex justify-center items-center h-[calc(100vh-12rem)]">
                        <div class="text-center">
                            <img :src="'/images/no-results.png'" class="w-64 mx-auto">
                            <div class="flex flex-col mt-3">
                                <h3 class="text-lg font-medium text-brand">Nu există date disponibile</h3>
                                <p> Nu au fost găsite înregistrări pentru perioada selectată.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Data State -->
                    <div v-else class="pt-8 flow-root">
                        <div class="flex flex-col space-y-1.5 mb-6">
                            <div class="text-sm">
                                Număr zile lucrătoare / lună
                                <span class="font-semibold">
                                    {{ date.toLocaleString('ro-RO', { month: 'long' }).toUpperCase() }} =
                                    {{ monthDetails.workingDays }} zile
                                </span>
                            </div>
                            <div class="text-sm">
                                Total ore / lună
                                <span class="font-semibold">
                                    {{ date.toLocaleString('ro-RO', { month: 'long' }).toUpperCase() }} =
                                    {{ monthDetails.totalHours }} ore
                                </span>
                            </div>
                        </div>

                        <!-- Main Table -->
                        <div class="overflow-hidden">
                            <table class="table-auto border border-collapse min-w-full">
                                <thead>
                                    <tr>
                                        <th class="border p-1.5 text-xs" rowspan="2">Nr. crt.</th>
                                        <th class="border p-1.5 text-xs">Data/ziua</th>
                                        <th v-for="day in monthDetails.daysInMonth" :key="day" class="border p-1.5 text-xs font-semibold">
                                            {{ day }}
                                        </th>
                                        <th class="border p-1.5 text-xs" rowspan="2">TOTAL ORE <br/>lucrate</th>
                                        <th class="border p-1.5 text-xs" rowspan="2">TOTAL ORE <br/>Spor condiții grele</th>
                                        <th class="border p-1.5 text-xs" rowspan="2">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <div>OBSERVAȚII</div>
                                                <i class="pi pi-info-circle text-brand" v-tooltip.bottom="'CO, CM, CS, Î, P, CP, M, S, D, CIC, PR, L, DS, LS, R'"></i>
                                            </div>
                                        </th>
                                    </tr>
                                    <tr>
                                        <th class="border p-1.5 text-xs">Numele și Prenumele</th>
                                        <th v-for="day in monthDetails.daysInMonth" :key="`initial-${day}`" class="border p-1.5 text-xs uppercase font-semibold">
                                            {{ getDayInitial(day) }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(person, index) in reportData.people"
                                        :key="`print-person-${index}`"
                                        :class="{ 'avoid-break': hasMultipleLines(person) }"
                                    >
                                        <td class="border p-1.5 text-center text-sm">{{ index + 1 }}</td>
                                        <td class="border p-1.5 text-left text-sm whitespace-nowrap">{{ formatName(person.name) }}</td>
                                        <td
                                            v-for="day in monthDetails.daysInMonth"
                                            :key="`hours-${day}`"
                                            :class="{ 'bg-gray-100': isWeekend(day) }"
                                            class="border p-1.5 text-center text-sm whitespace-pre-line leading-3"
                                        >
                                            <template v-if="typeof person.hours[day] === 'number'">
                                                {{ person.display_codes?.[day] || (person.hours[day] === 0 ? '' : person.hours[day]) }}
                                            </template>
                                            <template v-else>
                                                <span class="font-semibold text-xs leading-3">{{ person.hours[day] }}</span>
                                            </template>
                                        </td>
                                        <td class="border p-1.5 text-center text-sm">{{ person.totalHours }}</td>
                                        <td class="border p-1.5 text-center text-sm">{{ person.spor }}</td>
                                        <td class="border p-2 text-left text-xs whitespace-nowrap cursor-pointer hover:bg-gray-50"
                                            @click="openDetailsEditor(person)">
                                            <!-- If we have edited details, show those -->
                                            <ul v-if="getEditedDetails(person)" class="list-none">
                                                <li v-for="(detail, detailIndex) in getEditedDetails(person)"
                                                    :key="`edited-detail-${detailIndex}`"
                                                    class="mb-0.5 last:mb-0 font-medium text-blue-700"
                                                    v-html="detail">
                                                </li>
                                            </ul>
                                            <!-- Otherwise show original details -->
                                            <ul v-else class="list-none">
                                                <li v-for="(detail, detailIndex) in person.details"
                                                    :key="`detail-${detailIndex}`"
                                                    class="mb-0.5 last:mb-0"
                                                    v-html="detail">
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <Dialog v-model:visible="detailsEditorVisible" header="Editare observații" :modal="true" :style="{ width: '40rem' }">
                                <div class="p-fluid">
                                    <div class="mb-3">
                                        <h3 class="font-semibold">{{ currentPerson?.name }}</h3>
                                        <p class="text-sm text-gray-500">Editează observații pentru această persoană</p>
                                    </div>

                                    <div class="field mb-6">
                                        <div class="bg-gray-50 p-3 rounded text-sm">
                                            <ul class="list-none">
                                                <li v-for="(detail, idx) in currentPersonDetails"
                                                    :key="idx"
                                                    class="mb-1"
                                                    v-html="detail">
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="edited-details" class="block font-medium mb-2">Adaugă observații</label>
                                        <div class="border rounded p-3 bg-white">
                                            <!-- Dynamic inputs for each detail -->
                                            <div v-for="(detail, index) in editedDetails" :key="index" class="mb-2 flex items-center gap-2">
                                                <InputText
                                                    v-model="editedDetails[index]"
                                                    class="flex-grow"
                                                    placeholder="adaugă observație"
                                                />
                                                <Button
                                                    icon="pi pi-times"
                                                    class="p-button-rounded p-button-text p-button-sm"
                                                    @click="removeDetail(index)"
                                                    v-tooltip="'Șterge'"
                                                />
                                            </div>

                                            <!-- Add new detail button -->
                                            <Button
                                                label="Adaugă observație nouă"
                                                icon="pi pi-plus"
                                                class="p-button-text p-button-sm mt-2"
                                                @click="addDetail()"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <template #footer>
                                    <Button
                                        label="Anulează"
                                        icon="pi pi-times"
                                        @click="closeDetailsEditor"
                                        class="p-button-text"
                                    />
                                    <Button
                                        label="Salvează"
                                        icon="pi pi-check"
                                        @click="saveDetailsEdit"
                                        class="p-button-primary"
                                    />
                                </template>
                            </Dialog>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- PRINT TABLE -->
        <div class="hidden print:block font-times">
            <!-- Header Section -->
            <div class="flex justify-between mb-8">
                <!-- Left Header -->
                <div class="text-center font-bold text-xs uppercase space-y-0.5">
                    <div>Ministerul Afacerilor Interne</div>
                    <div>Inspectoratul General al Poliției Române</div>
                    <div>Școala de Agenți de Poliție „Vasile Lascăr" Câmpina</div>
                    <div>{{ selectedBusinessUnitGroup?.name || '...' }}</div>
                </div>

                <!-- Right Header -->
                <div class="text-xs text-center space-y-1.5">
                    <div class="uppercase">Nesecret</div>
                    <div>Nr. _____________ din _____.{{ date.getFullYear() }}</div>
                    <div>Ex. ____ /</div>
                </div>
            </div>

            <!-- Approval Section -->
            <div class="flex justify-end mr-60 mt-5">
                <div class="text-xs text-center">
                    <div class="font-bold text-base uppercase">Aprob</div>
                    <div class="font-bold text-base uppercase">Directorul școlii</div>
                    <div class="mt-0 text-base italic">Chestor de poliție</div>
                    <div class="font-bold text-base uppercase mt-3">Dr. <span class="font-bold">TACHE VASILE</span></div>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-center text-lg uppercase max-w-3xl mx-auto mt-10">
                <span class="underline font-bold">{{ selectedBusinessUnitGroup?.name || '...' }}</span><br>
                Situația prezenței la program a personalului în luna {{ date.toLocaleString('ro-RO', { month: 'long' }) }}
                {{ date.getFullYear() }}
            </h2>

            <!-- Month Details -->
            <div class="flex flex-col mt-5 text-sm">
                <div>
                    Număr zile lucrătoare / lună
                    <span class="font-bold">
                        {{ date.toLocaleString('ro-RO', { month: 'long' }).toUpperCase() }} =
                        {{ monthDetails.workingDays }} zile
                    </span>
                </div>
                <div>
                    Total ore / lună
                    <span class="font-bold">
                        {{ date.toLocaleString('ro-RO', { month: 'long' }).toUpperCase() }} =
                        {{ monthDetails.totalHours }} ore
                    </span>
                </div>
            </div>

            <!-- Main Table -->
            <table class="table-auto border border-black/80 border-collapse w-full mt-5">
                <thead>
                    <tr>
                        <th class="border border-black/80 p-1.5 text-xs" rowspan="2">Nr. crt.</th>
                        <th class="border border-black/80 p-1.5 text-xs">Data/ziua</th>
                        <th
                            v-for="day in monthDetails.daysInMonth"
                            :key="`print-${day}`"
                            class="border border-black/80 p-1.5 text-xs w-7"
                        >
                            {{ day }}
                        </th>
                        <th class="border border-black/80 p-1.5 text-xs" rowspan="2">TOTAL ORE<br/>lucrate</th>
                        <th class="border border-black/80 p-1.5 text-xs" rowspan="2">TOTAL ORE<br/>Spor condiții grele</th>
                        <th class="border border-black/80 p-1.5 text-xs" rowspan="2">
                            OBS.<br/>(CO, CM, CS, Î, P, CP, M, S, D, CIC, PR, L, DS, LS, R)
                        </th>
                    </tr>
                    <tr>
                        <th class="border border-black/80 p-1.5 text-xs">Numele și Prenumele</th>
                        <th
                            v-for="day in monthDetails.daysInMonth"
                            :key="`print-initial-${day}`"
                            class="border border-black/80 p-1.5 text-xs uppercase w-7"
                        >
                            {{ getDayInitial(day) }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(person, index) in reportData.people"
                        :key="`print-person-${index}`"
                        :class="{ 'avoid-break': hasMultipleLines(person) }"
                    >
                        <td class="border border-black/80 p-1.5 text-center text-sm">{{ index + 1 }}</td>
                        <td class="border border-black/80 p-1.5 text-left text-sm whitespace-nowrap">{{ formatName(person.name) }}</td>
                        <td
                            v-for="day in monthDetails.daysInMonth"
                            :key="`print-hours-${day}`"
                            :class="{ 'bg-gray-300': isWeekend(day) }"
                            class="border border-black/80 p-1.5 text-center text-sm w-7"
                        >
                            {{ person.display_codes?.[day] || formatHours(person.hours[day]) }}
                        </td>
                        <td class="border border-black/80 p-1.5 text-center text-sm">{{ person.totalHours }}</td>
                        <td class="border border-black/80 p-1.5 text-center text-sm">{{ person.spor }}</td>
                        <td class="border border-black/80 p-2 text-left text-xs whitespace-nowrap">
                            <ul class="list-none">
                                <li
                                    v-for="(detail, detailIndex) in getEditedDetails(person) || person.details"
                                    :key="`print-detail-${detailIndex}`"
                                    class="mb-0.5 last:mb-0"
                                    v-html="detail"
                                >
                                </li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Footer Note -->
            <div class="mt-6 text-xs leading-5">
                NOTĂ: Din numărul de 8 ore/zi vor fi scăzute orele corespunzătoare situației în care se poate afla personalul școlii:
                concediu de odihnă (CO), concediu de studiu (CS), concediu medical (CM), învoiri (Î) și permisii (P),
                cursuri de pregătire (CP), misiuni în afara garnizoanei Câmpina (M), seminar (S), documentare (D),
                concediu pentru îngrijirea copilului (CIC), program redus (PR), zilele libere fără plată (L),
                deplasări în străinătate (DS), liber după serviciu (LS), recuperare (R) sau alte situații.
            </div>

            <!-- Signatures -->
            <div class="flex justify-between px-10 mt-12">
                <div class="text-center">
                    <div class="font-bold text-base uppercase">Întocmit</div>
                    <div class="font-bold text-base uppercase">Agent II</div>
                    <div class="mt-0 text-base italic">Agent-șef principal de poliție</div>
                    <div class="font-bold text-base uppercase mt-3">ANGHEL BOGDAN</div>
                </div>

                <div class="text-center">
                    <div class="font-bold text-base uppercase">De acord</div>
                    <div class="font-bold text-base uppercase">Șef birou</div>
                    <div class="mt-0 text-base italic">Comisar-șef de poliție</div>
                    <div class="font-bold text-base uppercase mt-3">Mihalea Andrei</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>

import { ref, computed, watch, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'

import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'

import Header from '@/Components/shared/c-page-header.vue'
import SidebarMenu from '@/Components/partials/c-sidebar-menu.vue'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import axios from 'axios'

const toast = useToast()

// Editing cells
const detailsEdits = ref({})
const detailsEditorVisible = ref(false)
const currentPerson = ref(null)
const editedDetails = ref([])

// State
const selectedBusinessUnitGroup = ref(null)
const date = ref(new Date())
const loading = ref(false)
const error = ref(null)
const reportData = ref({
    people: [],
    month_details: {
        working_days: 0,
        total_hours: 0,
    },
})

defineProps({
    businessUnitGroups: {
        type: Array,
        required: true,
    },
    people: {
        type: Array,
        required: true,
    },
})

// Editing cells
const currentPersonDetails = computed(() => {
    if (!currentPerson.value) return []
    return currentPerson.value.details || []
})

const getEditedDetails = (person) => {
    if (!person) return null

    // Find the person's index
    const personIndex = reportData.value.people.findIndex(p => p === person)
    if (personIndex === -1) return null

    // Get edits using the index key
    const personKey = `person_${personIndex}`
    return detailsEdits.value[personKey] || null
}

const openDetailsEditor = (person) => {
    currentPerson.value = person

    // Find the person's index
    const personIndex = reportData.value.people.findIndex(p => p === person)
    if (personIndex === -1) {
        toast.error('Nu s-a putut găsi persoana în lista de date.')
        return
    }

    // Use the same key format
    const personKey = `person_${personIndex}`

    // Initialize with either existing edits or original details
    if (detailsEdits.value[personKey]) {
        editedDetails.value = [...detailsEdits.value[personKey]]
    } else {
        editedDetails.value = [...(person.details || [])]
    }

    // Show dialog
    detailsEditorVisible.value = true
}

const closeDetailsEditor = () => {
    detailsEditorVisible.value = false
    currentPerson.value = null
    editedDetails.value = []
}

const addDetail = () => {
    editedDetails.value.push('')
}

const removeDetail = (index) => {
    editedDetails.value.splice(index, 1)
}

const saveEditsToStorage = () => {
    try {
        const storageKey = `monthly-report-edits-${selectedBusinessUnitGroup.value?.id}-${date.value.getFullYear()}-${date.value.getMonth() + 1}`
        localStorage.setItem(storageKey, JSON.stringify(detailsEdits.value))
    } catch (error) {
        console.error('Error saving edits to localStorage:', error)
    }
}

const loadEditsFromStorage = () => {
    try {
        if (!selectedBusinessUnitGroup.value || !date.value) return

        const storageKey = `monthly-report-edits-${selectedBusinessUnitGroup.value?.id}-${date.value.getFullYear()}-${date.value.getMonth() + 1}`
        const savedEdits = localStorage.getItem(storageKey)

        if (savedEdits) {
            detailsEdits.value = JSON.parse(savedEdits)
        }
    } catch (error) {
        console.error('Error loading edits from localStorage:', error)
    }
}

const clearAllEdits = () => {
    if (confirm('Ești sigur că dorești să ștergi toate modificările?')) {
        detailsEdits.value = {}
        saveEditsToStorage() // Clear localStorage too
        toast.success('Toate modificările au fost șterse', {
            timeout: 2000,
            position: 'bottom-right',
        })
    }
}

const hasEdits = computed(() => {
    return Object.keys(detailsEdits.value).length > 0
})

const saveDetailsEdit = () => {
    try {
        // Check if we have a valid person reference
        if (!currentPerson.value) {
            toast.error('Nu s-a putut identifica persoana selectată.')
            return
        }

        // Filter out empty details
        const filteredDetails = editedDetails.value
            .map(detail => detail.trim())
            .filter(detail => detail !== '')

        // Find person's index in the report data
        const personIndex = reportData.value.people.findIndex(p => p === currentPerson.value)

        if (personIndex === -1) {
            toast.error('Nu s-a putut găsi persoana în lista de date.')
            return
        }

        // Use the index as the key
        const personKey = `person_${personIndex}`

        // If we have a different set of details than the original
        const originalDetailsString = JSON.stringify(currentPerson.value.details || [])
        const newDetailsString = JSON.stringify(filteredDetails)

        if (originalDetailsString !== newDetailsString) {
            // Save edited details using the index key
            detailsEdits.value[personKey] = filteredDetails

            // Save to localStorage
            saveEditsToStorage()

            // Show success message
            toast.success('Observații salvate cu succes!', {
                timeout: 2000,
                position: 'bottom-right',
            })
        } else {
            // If identical to original, remove any stored edits
            if (personKey in detailsEdits.value) {
                delete detailsEdits.value[personKey]

                // Save to localStorage
                saveEditsToStorage()
            }
            toast.success('Nu s-au făcut modificări', {
                timeout: 2000,
                position: 'bottom-right',
            })
        }
    } catch (error) {
        console.error('Error saving details:', error)
        toast.error('A apărut o eroare la salvarea observațiilor', {
            timeout: 2000,
            position: 'bottom-right',
        })
    } finally {
        // Always close the editor when done
        closeDetailsEditor()
    }
}

// OTHER CONSTS
const getDayInitial = (day) => {
    const currentDate = new Date(date.value.getFullYear(), date.value.getMonth(), day)
    return currentDate.toLocaleDateString('ro-RO', { weekday: 'short' }).charAt(0)
}

const isWeekend = (day) => {
    const currentDate = new Date(date.value.getFullYear(), date.value.getMonth(), day)
    const dayOfWeek = currentDate.getDay()
    return dayOfWeek === 0 || dayOfWeek === 6
}

const formatHours = (hours) => {
    if (!hours || hours === 0) return ''
    return hours
}

const formatName = (name) => {
    if (!name) return ''
    return name
}

const printTable = () => {
    window.print()
}

const monthDetails = computed(() => ({
    daysInMonth: new Date(date.value.getFullYear(), date.value.getMonth() + 1, 0).getDate(),
    workingDays: reportData.value?.month_details?.working_days || 0,
    totalHours: reportData.value?.month_details?.total_hours || 0,
}))

// Add this to your script setup section, with the other functions
const hasMultipleLines = (person) => {
    // Consider a person has multiple lines if they have more than one detail
    return person.details && person.details.length > 1
}

// Methods
const fetchData = async () => {
    // Don't fetch if no business unit group is selected
    if (!selectedBusinessUnitGroup.value) {
        reportData.value = {
            people: [],
            month_details: {
                working_days: 0,
                total_hours: 0,
            },
        }
        return
    }

    try {
        loading.value = true
        error.value = null

        if (!date.value) {
            date.value = new Date()
        }

        const year = date.value.getFullYear()
        const month = date.value.getMonth() + 1

        const startOfMonth = `${year}-${month.toString().padStart(2, '0')}-01`
        const lastDay = new Date(year, month, 0).getDate()
        const endOfMonth = `${year}-${month.toString().padStart(2, '0')}-${lastDay}`

        const response = await axios.get('/rapoarte/prezenta-lunara/data', {
            params: {
                start_date: startOfMonth,
                end_date: endOfMonth,
                business_unit_group_id: selectedBusinessUnitGroup.value?.id,
            },
        })

        // Make sure to set the entire reportData structure
        reportData.value = {
            people: response.data.people || [],
            month_details: response.data.month_details || {
                working_days: 0,
                total_hours: 0,
            },
        }
    } catch (e) {
        error.value = 'A apărut o eroare la încărcarea datelor.'
        console.error('Error fetching data:', e)
    } finally {
        loading.value = false
    }
}

watch([date, selectedBusinessUnitGroup], () => {
    if (loading.value) return
    fetchData()
}, { deep: true })

watch(() => reportData.value.people, () => {
    if (reportData.value.people?.length > 0) {
        loadEditsFromStorage()
    }
}, { immediate: true })

// Initial data fetch
onMounted(() => {
    if (!reportData.value.people?.length) {
        fetchData()
    }
})

</script>

<style scoped>
@media print {
    @page {
        size: landscape;
        margin: 1cm;
    }
}

.avoid-break {
    page-break-inside: avoid;
}

.edited-detail {
  color: #4338ca;
  font-weight: 500;
}

@media print {
  .edited-detail {
    color: black !important;
    font-weight: normal;
  }
}
</style>
