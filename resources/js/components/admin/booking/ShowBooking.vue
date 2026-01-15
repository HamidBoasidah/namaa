<template>
  <div class="space-y-6">
    <!-- Header Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
          <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            {{ t('bookings.booking') }} #{{ booking.id }}
          </h2>
          <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ formatDateTime(booking.start_at) }}
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
          <span
            class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium"
            :class="getStatusClass(booking.status)"
          >
            {{ t(`bookings.statuses.${booking.status}`) }}
          </span>
          <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
            {{ booking.duration_minutes }} {{ t('common.minutes') }}
          </span>
        </div>
      </div>
    </div>

    <!-- Client & Consultant Info -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
      <!-- Client Info -->
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('bookings.clientInfo') }}</h3>
        </div>
        <div class="p-4 sm:p-6">
          <div class="flex items-center gap-4">
            <img
              v-if="booking.client_avatar"
              :src="booking.client_avatar"
              class="h-16 w-16 rounded-full object-cover"
            />
            <div v-else class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
              <span class="text-xl text-gray-500">{{ getInitials(booking.client_name) }}</span>
            </div>
            <div>
              <p class="text-base font-medium text-gray-800 dark:text-white/90">{{ booking.client_name || '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ booking.client_email || '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ booking.client_phone || '-' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Consultant Info -->
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('bookings.consultantInfo') }}</h3>
        </div>
        <div class="p-4 sm:p-6">
          <div class="flex items-center gap-4">
            <img
              v-if="booking.consultant_avatar"
              :src="booking.consultant_avatar"
              class="h-16 w-16 rounded-full object-cover"
            />
            <div v-else class="h-16 w-16 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
              <span class="text-xl text-gray-500">{{ getInitials(booking.consultant_name) }}</span>
            </div>
            <div>
              <p class="text-base font-medium text-gray-800 dark:text-white/90">{{ booking.consultant_name || '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ booking.consultant_email || '-' }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ booking.consultant_phone || '-' }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Details -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('bookings.bookingDetails') }}</h3>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.service') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ booking.bookable_title || t('bookings.directConsultation') }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.bookableType') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ t(`bookings.bookableTypes.${booking.bookable_type_short}`) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.startAt') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ formatDateTime(booking.start_at) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.endAt') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ formatDateTime(booking.end_at) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.duration') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ booking.duration_minutes }} {{ t('common.minutes') }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.buffer') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ booking.buffer_after_minutes }} {{ t('common.minutes') }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.status') }}</label>
            <span
              class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
              :class="getStatusClass(booking.status)"
            >
              {{ t(`bookings.statuses.${booking.status}`) }}
            </span>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.createdAt') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ formatDateTime(booking.created_at) }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('bookings.notes') }}</label>
            <p class="whitespace-pre-line text-base text-gray-800 dark:text-white/90">{{ booking.notes || '—' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Cancellation Info (if cancelled) -->
    <div v-if="booking.status === 'cancelled'" class="rounded-2xl border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20">
      <div class="border-b border-red-200 px-6 py-4 dark:border-red-800">
        <h3 class="text-lg font-medium text-red-800 dark:text-red-400">{{ t('bookings.cancellationInfo') }}</h3>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-red-600 dark:text-red-400">{{ t('bookings.cancelledAt') }}</label>
            <p class="text-base text-red-800 dark:text-red-300">{{ formatDateTime(booking.cancelled_at) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-red-600 dark:text-red-400">{{ t('bookings.cancelledBy') }}</label>
            <p class="text-base text-red-800 dark:text-red-300">{{ booking.cancelled_by_name || '—' }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-red-600 dark:text-red-400">{{ t('bookings.cancelReason') }}</label>
            <p class="whitespace-pre-line text-base text-red-800 dark:text-red-300">{{ booking.cancel_reason || '—' }}</p>
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
      <Link
        :href="route('admin.bookings.edit', booking.id)"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white"
      >
        {{ t('buttons.edit') }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { route } from '@/route'

const { t } = useI18n()

const props = defineProps({
  booking: { type: Object, required: true },
})

const booking = computed(() => props.booking || {})

function getStatusClass(status) {
  const classes = {
    pending: 'bg-yellow-50 text-yellow-600 dark:bg-yellow-500/15 dark:text-yellow-400',
    confirmed: 'bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-400',
    completed: 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400',
    cancelled: 'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400',
    expired: 'bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400',
  }
  return classes[status] || classes.pending
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDateTime(dateTime) {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleString('ar-SA', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}
</script>
