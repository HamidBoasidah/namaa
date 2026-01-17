<template>
  <form class="space-y-6" @submit.prevent="update">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">
          {{ t('bookings.editBooking') }}
        </h2>
      </div>

      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <!-- Client (Read-only) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.client') }}
            </label>
            <input
              :value="booking.client_name"
              type="text"
              disabled
              class="dark:bg-gray-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 cursor-not-allowed"
            />
          </div>

          <!-- Consultant (Read-only) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.consultant') }}
            </label>
            <input
              :value="booking.consultant_name"
              type="text"
              disabled
              class="dark:bg-gray-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 cursor-not-allowed"
            />
          </div>

          <!-- Service (Read-only) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.service') }}
            </label>
            <input
              :value="booking.bookable_title || t('bookings.directConsultation')"
              type="text"
              disabled
              class="dark:bg-gray-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90 cursor-not-allowed"
            />
          </div>

          <!-- Consultation Method (Read-only) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.consultationMethod') }}
            </label>
            <div class="dark:bg-gray-900/50 shadow-theme-xs h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400 flex items-center gap-2">
              <component :is="getMethodIcon(booking.consultation_method)" class="h-5 w-5" />
              {{ t(`bookings.methods.${booking.consultation_method}`) }}
            </div>
          </div>

          <!-- Duration (Read-only display) -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('bookings.duration') }}
            </label>
            <div class="dark:bg-gray-900/50 shadow-theme-xs h-11 w-full rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-400 flex items-center">
              {{ form.duration_minutes }} {{ t('common.minutes') }}
            </div>
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
                        :disabled="isPastDate(day)"
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
        {{ form.processing ? t('common.loading') : t('buttons.update') }}
      </button>
    </div>
  </form>
</template>


<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import { route } from '@/route'
import axios from 'axios'
import { VideoIcon, AudioIcon, TextIcon } from '@/icons'

const { t, locale } = useI18n()
const { success, error } = useNotifications()

// Get icon component based on consultation method
function getMethodIcon(method) {
  switch (method) {
    case 'video': return VideoIcon
    case 'audio': return AudioIcon
    case 'text': return TextIcon
    default: return VideoIcon
  }
}

const props = defineProps({
  booking: { type: Object, required: true },
  consultants: { type: Array, required: true },
  clients: { type: Array, required: true },
  services: { type: Array, default: () => [] },
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
  
  if (isPast) {
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
  if (isPastDate(date)) return
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
  if (!props.booking.consultant_id || !selectedDate.value) {
    timeSlots.value = []
    return
  }
  
  loadingSlots.value = true
  
  try {
    const dateStr = selectedDate.value.toISOString().split('T')[0]
    const url = route('admin.bookings.available-slots')
    
    const response = await axios.get(url, {
      params: {
        consultant_id: props.booking.consultant_id,
        date: dateStr,
        duration: form.duration_minutes,
        bookable_type: props.booking.bookable_type,
        bookable_id: props.booking.bookable_id || null,
        exclude_booking_id: props.booking.id, // Exclude current booking from conflict check
      }
    })
    
    timeSlots.value = response.data.slots || []
  } catch (err) {
    console.error('Error fetching slots:', err)
    // Generate default slots if API fails
    timeSlots.value = generateDefaultSlots()
  } finally {
    loadingSlots.value = false
  }
}

// Generate default time slots (fallback) - all marked as unavailable since we couldn't fetch real data
function generateDefaultSlots() {
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
  { value: 'completed', label: t('bookings.statuses.completed') },
  { value: 'cancelled', label: t('bookings.statuses.cancelled') },
])

// Format datetime for input
function formatDateTimeForInput(dateTime) {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toISOString().slice(0, 16)
}

const form = useForm({
  _method: 'PUT',
  start_at: formatDateTimeForInput(props.booking?.start_at),
  duration_minutes: props.booking?.duration_minutes ?? 60,
  buffer_after_minutes: props.booking?.buffer_after_minutes ?? 0,
  status: props.booking?.status ?? 'confirmed',
  notes: props.booking?.notes ?? '',
})

// Initialize calendar with booking's date and time
function initializeFromBooking() {
  if (props.booking?.start_at) {
    const bookingDate = new Date(props.booking.start_at)
    selectedDate.value = new Date(bookingDate.getFullYear(), bookingDate.getMonth(), bookingDate.getDate())
    currentDate.value = new Date(bookingDate.getFullYear(), bookingDate.getMonth(), 1)
    
    // Set selected time
    const hours = String(bookingDate.getHours()).padStart(2, '0')
    const minutes = String(bookingDate.getMinutes()).padStart(2, '0')
    selectedTime.value = `${hours}:${minutes}`
    
    // Fetch available slots for the booking date
    fetchAvailableSlots()
  }
}

// Initialize on mount
onMounted(() => {
  initializeFromBooking()
})

function update() {
  form.post(route('admin.bookings.update', props.booking.id), {
    onSuccess: () => success(t('bookings.updatedSuccessfully')),
    onError: () => error(t('bookings.updateFailed')),
    preserveScroll: true,
  })
}
</script>
