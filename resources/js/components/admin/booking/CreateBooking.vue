<template>
  <form class="space-y-6" @submit.prevent="create">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">
          {{ t('bookings.addBooking') }}
        </h2>
      </div>

      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <!-- Client -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.client') }} <span class="text-error-500">*</span>
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.client_id"
                class="dark:bg-gray-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
              >
                <option value="" disabled>{{ t('bookings.selectClient') }}</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
            <p v-if="form.errors.client_id" class="mt-1 text-sm text-error-500">{{ form.errors.client_id }}</p>
          </div>

          <!-- Consultant -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.consultant') }} <span class="text-error-500">*</span>
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.consultant_id"
                class="dark:bg-gray-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
              >
                <option value="" disabled>{{ t('bookings.selectConsultant') }}</option>
                <option v-for="consultant in consultants" :key="consultant.id" :value="consultant.id">
                  {{ consultant.name }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
            <p v-if="form.errors.consultant_id" class="mt-1 text-sm text-error-500">{{ form.errors.consultant_id }}</p>
          </div>

          <!-- Bookable Type -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.bookableType') }} <span class="text-error-500">*</span>
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.bookable_type"
                class="dark:bg-gray-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
              >
                <option v-for="type in bookableTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
            <p v-if="form.errors.bookable_type" class="mt-1 text-sm text-error-500">{{ form.errors.bookable_type }}</p>
          </div>

          <!-- Service (if consultant_service) -->
          <div v-if="form.bookable_type === 'consultant_service'" class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.service') }} <span class="text-error-500">*</span>
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.bookable_id"
                class="dark:bg-gray-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
              >
                <option value="" disabled>{{ t('bookings.selectService') }}</option>
                <option v-for="service in filteredServices" :key="service.id" :value="service.id">
                  {{ service.title }} ({{ service.duration_minutes }} {{ t('common.minutes') }})
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
            <p v-if="form.errors.bookable_id" class="mt-1 text-sm text-error-500">{{ form.errors.bookable_id }}</p>
          </div>

          <!-- Date & Time Picker Section -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.selectDateTime') }} <span class="text-error-500">*</span>
            </label>
            
            <!-- Calendar and Time Slots Container -->
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
              <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Calendar -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                  <!-- Calendar Header -->
                  <div class="mb-4 flex items-center justify-between">
                    <button 
                      type="button"
                      @click="previousMonth"
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                    >
                      <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                      </svg>
                    </button>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                      {{ currentMonthName }} {{ currentYear }}
                    </h3>
                    <button 
                      type="button"
                      @click="nextMonth"
                      class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                    >
                      <svg class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                  </div>

                  <!-- Week Days Header -->
                  <div class="mb-2 grid grid-cols-7 gap-1">
                    <div 
                      v-for="day in weekDays" 
                      :key="day" 
                      class="py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400"
                    >
                      {{ day }}
                    </div>
                  </div>

                  <!-- Calendar Days -->
                  <div class="grid grid-cols-7 gap-1">
                    <template v-for="(day, index) in calendarDays" :key="index">
                      <button
                        v-if="day"
                        type="button"
                        @click="selectDate(day)"
                        :disabled="isPastDate(day) || !form.consultant_id"
                        class="flex h-10 w-full items-center justify-center rounded-lg text-sm font-medium transition"
                        :class="getDayClass(day)"
                      >
                        {{ day.getDate() }}
                      </button>
                      <div v-else class="h-10"></div>
                    </template>
                  </div>

                  <!-- Selected Date Display -->
                  <div v-if="selectedDate" class="mt-4 rounded-lg bg-brand-50 p-3 dark:bg-brand-500/10">
                    <p class="text-center text-sm font-medium text-brand-700 dark:text-brand-400">
                      {{ formatSelectedDate }}
                    </p>
                  </div>
                </div>

                <!-- Time Slots -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                  <h4 class="mb-4 text-sm font-semibold text-gray-800 dark:text-white">
                    {{ t('bookings.selectTime') }}
                  </h4>

                  <!-- Loading State -->
                  <div v-if="loadingSlots" class="flex items-center justify-center py-8">
                    <svg class="h-8 w-8 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                  </div>

                  <!-- No Consultant Selected -->
                  <div v-else-if="!form.consultant_id" class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.selectConsultantFirst') }}</p>
                  </div>

                  <!-- No Date Selected -->
                  <div v-else-if="!selectedDate" class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.selectDateFirst') }}</p>
                  </div>

                  <!-- No Available Slots -->
                  <div v-else-if="timeSlots.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="mb-2 h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.noAvailableSlots') }}</p>
                  </div>

                  <!-- Time Slots Grid -->
                  <div v-else class="grid grid-cols-3 gap-2 sm:grid-cols-4">
                    <button
                      v-for="slot in timeSlots"
                      :key="slot.time"
                      type="button"
                      @click="selectTime(slot)"
                      :disabled="!slot.available"
                      :title="getSlotTooltip(slot)"
                      class="rounded-lg border px-3 py-2.5 text-sm font-medium transition"
                      :class="getSlotClass(slot)"
                    >
                      {{ slot.display }}
                    </button>
                  </div>

                  <!-- Legend -->
                  <div class="mt-4 flex flex-wrap gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                      <span class="h-3 w-3 rounded border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800"></span>
                      <span class="text-gray-600 dark:text-gray-400">{{ t('bookings.available') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="h-3 w-3 rounded border border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-900/20"></span>
                      <span class="text-gray-600 dark:text-gray-400">{{ t('bookings.booked') }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                      <span class="h-3 w-3 rounded border border-gray-200 bg-gray-100 dark:border-gray-700 dark:bg-gray-800"></span>
                      <span class="text-gray-600 dark:text-gray-400">{{ t('bookings.passed') }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <p v-if="form.errors.start_at" class="mt-1 text-sm text-error-500">{{ form.errors.start_at }}</p>
          </div>

          <!-- Duration -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.duration') }} <span class="text-error-500">*</span>
            </label>
            <input
              v-model.number="form.duration_minutes"
              type="number"
              min="5"
              step="5"
              class="dark:bg-gray-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
            />
            <p v-if="form.errors.duration_minutes" class="mt-1 text-sm text-error-500">{{ form.errors.duration_minutes }}</p>
          </div>

          <!-- Buffer (Read-only - auto-filled) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.buffer') }}
            </label>
            <div class="dark:bg-gray-900/50 shadow-theme-xs h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400 flex items-center">
              {{ form.buffer_after_minutes }} {{ t('common.minutes') }}
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              {{ t('bookings.bufferAutoFilled') }}
            </p>
          </div>

          <!-- Status -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.status') }}
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.status"
                class="dark:bg-gray-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
              >
                <option v-for="status in statuses" :key="status.value" :value="status.value">
                  {{ status.label }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
            </div>
          </div>

          <!-- Notes -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.notes') }}
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="dark:bg-gray-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90"
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-sm text-error-500">{{ form.errors.notes }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link
        :href="route('admin.bookings.index')"
        class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700"
      >
        {{ t('buttons.backToList') }}
      </Link>

      <button
        type="submit"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white"
        :class="{ 'cursor-not-allowed opacity-70': form.processing }"
        :disabled="form.processing"
      >
        {{ form.processing ? t('common.loading') : t('buttons.create') }}
      </button>
    </div>
  </form>
</template>


<script setup>
import { ref, computed, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import { route } from '@/route'
import axios from 'axios'

const { t, locale } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
  consultants: { type: Array, required: true },
  clients: { type: Array, required: true },
  services: { type: Array, default: () => [] },
  availableSlots: { type: Array, default: () => [] },
})

// Calendar State
const currentDate = ref(new Date())
const selectedDate = ref(null)
const selectedTime = ref(null)
const timeSlots = ref([])
const loadingSlots = ref(false)

// Week days based on locale
const weekDays = computed(() => {
  if (locale.value === 'ar') {
    return ['س', 'ج', 'خ', 'أ', 'ث', 'إ', 'ح']
  }
  return ['S', 'M', 'T', 'W', 'T', 'F', 'S']
})

// Arabic month names
const arabicMonths = [
  'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
  'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
]

const currentYear = computed(() => currentDate.value.getFullYear())
const currentMonth = computed(() => currentDate.value.getMonth())
const currentMonthName = computed(() => {
  if (locale.value === 'ar') {
    return arabicMonths[currentMonth.value]
  }
  return currentDate.value.toLocaleString('en', { month: 'long' })
})

// Generate calendar days
const calendarDays = computed(() => {
  const year = currentYear.value
  const month = currentMonth.value
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const daysInMonth = lastDay.getDate()
  const startingDay = firstDay.getDay()
  
  const days = []
  
  // Add empty slots for days before the first day of month
  for (let i = 0; i < startingDay; i++) {
    days.push(null)
  }
  
  // Add days of the month
  for (let day = 1; day <= daysInMonth; day++) {
    days.push(new Date(year, month, day))
  }
  
  return days
})

// Format selected date for display
const formatSelectedDate = computed(() => {
  if (!selectedDate.value) return ''
  return selectedDate.value.toLocaleDateString(locale.value === 'ar' ? 'ar-SA' : 'en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

// Check if date is in the past
function isPastDate(date) {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return date < today
}

// Check if date is selected
function isSelectedDate(date) {
  if (!selectedDate.value || !date) return false
  return date.toDateString() === selectedDate.value.toDateString()
}

// Check if date is today
function isToday(date) {
  if (!date) return false
  return date.toDateString() === new Date().toDateString()
}

// Get day button class
function getDayClass(day) {
  if (!day) return ''
  
  const isPast = isPastDate(day)
  const isSelected = isSelectedDate(day)
  const isTodayDate = isToday(day)
  
  if (isPast || !form.consultant_id) {
    return 'text-gray-300 cursor-not-allowed dark:text-gray-600'
  }
  
  if (isSelected) {
    return 'bg-brand-500 text-white hover:bg-brand-600'
  }
  
  if (isTodayDate) {
    return 'bg-brand-100 text-brand-700 hover:bg-brand-200 dark:bg-brand-500/20 dark:text-brand-400'
  }
  
  return 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
}

// Get time slot class
function getSlotClass(slot) {
  if (!slot.available) {
    // Different styles based on reason
    if (slot.reason === 'booked') {
      return 'border-red-200 bg-red-50 text-red-400 cursor-not-allowed dark:border-red-900 dark:bg-red-900/20 dark:text-red-500 line-through'
    }
    return 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500'
  }
  
  if (selectedTime.value === slot.time) {
    return 'border-brand-500 bg-brand-500 text-white'
  }
  
  return 'border-gray-200 bg-white text-gray-700 hover:border-brand-300 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-brand-500 dark:hover:bg-brand-500/10'
}

// Get tooltip for slot
function getSlotTooltip(slot) {
  if (!slot.available) {
    if (slot.reason === 'booked') {
      return t('bookings.slotBooked')
    }
    if (slot.reason === 'past') {
      return t('bookings.slotPassed')
    }
    return t('bookings.slotUnavailable')
  }
  return t('bookings.clickToSelect')
}

// Navigate months
function previousMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value - 1, 1)
}

function nextMonth() {
  currentDate.value = new Date(currentYear.value, currentMonth.value + 1, 1)
}

// Select date
function selectDate(date) {
  if (isPastDate(date) || !form.consultant_id) return
  selectedDate.value = date
  selectedTime.value = null
  fetchAvailableSlots()
}

// Select time
function selectTime(slot) {
  if (!slot.available) return
  selectedTime.value = slot.time
  updateFormStartAt()
}

// Update form.start_at when date and time are selected
function updateFormStartAt() {
  if (selectedDate.value && selectedTime.value) {
    const [hours, minutes] = selectedTime.value.split(':')
    const dateTime = new Date(selectedDate.value)
    dateTime.setHours(parseInt(hours), parseInt(minutes), 0, 0)
    
    // Format as YYYY-MM-DDTHH:mm for datetime-local input
    const year = dateTime.getFullYear()
    const month = String(dateTime.getMonth() + 1).padStart(2, '0')
    const day = String(dateTime.getDate()).padStart(2, '0')
    const hour = String(dateTime.getHours()).padStart(2, '0')
    const minute = String(dateTime.getMinutes()).padStart(2, '0')
    
    form.start_at = `${year}-${month}-${day}T${hour}:${minute}`
  }
}

// Fetch available time slots from API
async function fetchAvailableSlots() {
  if (!form.consultant_id || !selectedDate.value) {
    timeSlots.value = []
    return
  }
  
  loadingSlots.value = true
  
  try {
    const dateStr = selectedDate.value.toISOString().split('T')[0]
    const url = route('admin.bookings.available-slots')
    console.log('Fetching slots from:', url)
    console.log('Params:', {
      consultant_id: form.consultant_id,
      date: dateStr,
      duration: form.duration_minutes,
      bookable_type: form.bookable_type,
      bookable_id: form.bookable_id || null,
    })
    
    const response = await axios.get(url, {
      params: {
        consultant_id: form.consultant_id,
        date: dateStr,
        duration: form.duration_minutes,
        bookable_type: form.bookable_type,
        bookable_id: form.bookable_id || null,
      }
    })
    
    console.log('API Response:', response.data)
    console.log('Total slots:', response.data.slots?.length || 0)
    timeSlots.value = response.data.slots || []
    console.log('Available slots:', timeSlots.value.filter(s => s.available).length)
    console.log('Unavailable slots:', timeSlots.value.filter(s => !s.available).length)
    if (timeSlots.value.length > 0) {
      console.log('First slot:', timeSlots.value[0])
      console.log('Last slot:', timeSlots.value[timeSlots.value.length - 1])
    }
  } catch (err) {
    console.error('Error fetching slots:', err)
    console.error('Error response:', err.response?.data)
    console.error('Error status:', err.response?.status)
    // Generate default slots if API fails
    timeSlots.value = generateDefaultSlots()
  } finally {
    loadingSlots.value = false
  }
}

// Generate default time slots (fallback) - all marked as unavailable since we couldn't fetch real data
function generateDefaultSlots() {
  console.warn('Using fallback slots - API failed')
  const slots = []
  for (let hour = 8; hour < 22; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      const time = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`
      const displayHour = hour > 12 ? hour - 12 : hour
      const period = hour >= 12 ? (locale.value === 'ar' ? 'م' : 'PM') : (locale.value === 'ar' ? 'ص' : 'AM')
      slots.push({
        time,
        display: `${displayHour}:${String(minute).padStart(2, '0')} ${period}`,
        available: false,
        reason: 'unavailable'
      })
    }
  }
  return slots
}

// Statuses from translations
const statuses = computed(() => [
  { value: 'pending', label: t('bookings.statuses.pending') },
  { value: 'confirmed', label: t('bookings.statuses.confirmed') },
])

// Bookable types from translations
const bookableTypes = computed(() => [
  { value: 'consultant', label: t('bookings.bookableTypes.consultant') },
  { value: 'consultant_service', label: t('bookings.bookableTypes.consultant_service') },
])

const form = useForm({
  client_id: '',
  consultant_id: '',
  bookable_type: 'consultant',
  bookable_id: '',
  start_at: '',
  duration_minutes: 60,
  buffer_after_minutes: 0,
  status: 'confirmed',
  notes: '',
})

// Filter services by selected consultant
const filteredServices = computed(() => {
  if (!form.consultant_id) return props.services
  return props.services.filter(s => s.consultant_id === form.consultant_id)
})

// When bookable_type is consultant, set bookable_id to consultant_id and get buffer
watch(() => form.bookable_type, (newType) => {
  if (newType === 'consultant') {
    form.bookable_id = form.consultant_id
    if (form.consultant_id) {
      const consultant = props.consultants.find(c => c.id === form.consultant_id)
      if (consultant) {
        form.buffer_after_minutes = consultant.buffer ?? 0
      }
    }
  } else {
    form.bookable_id = ''
    form.buffer_after_minutes = 0
  }
  // Refresh slots when type changes
  if (selectedDate.value) {
    fetchAvailableSlots()
  }
})

watch(() => form.consultant_id, (newId) => {
  if (form.bookable_type === 'consultant') {
    form.bookable_id = newId
    if (newId) {
      const consultant = props.consultants.find(c => c.id === newId)
      if (consultant) {
        form.buffer_after_minutes = consultant.buffer ?? 0
      }
    }
  }
  // Reset service selection when consultant changes
  if (form.bookable_type === 'consultant_service') {
    form.bookable_id = ''
    form.buffer_after_minutes = 0
  }
  // Reset date/time selection and refresh slots
  selectedTime.value = null
  form.start_at = ''
  if (selectedDate.value) {
    fetchAvailableSlots()
  }
})

// When service is selected, update duration and buffer
watch(() => form.bookable_id, (newId) => {
  if (form.bookable_type === 'consultant_service' && newId) {
    const service = props.services.find(s => s.id === newId)
    if (service) {
      form.duration_minutes = service.duration_minutes
      form.buffer_after_minutes = service.buffer ?? 0
    }
  }
  // Refresh slots when service changes
  if (selectedDate.value) {
    fetchAvailableSlots()
  }
})

// Refresh slots when duration changes
watch(() => form.duration_minutes, () => {
  if (selectedDate.value && form.consultant_id) {
    fetchAvailableSlots()
  }
})

function create() {
  form.post(route('admin.bookings.store'), {
    onSuccess: () => success(t('bookings.createdSuccessfully')),
    onError: () => error(t('bookings.creationFailed')),
    preserveScroll: true,
  })
}
</script>
