<template>
  <div class="space-y-6">
    <!-- Stats Summary -->
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
      <div class="rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50 to-amber-100/50 p-4 dark:border-amber-500/30 dark:from-amber-500/10 dark:to-amber-500/5">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-500/20 text-amber-600 dark:text-amber-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-400">{{ statusCounts.pending }}</p>
            <p class="text-xs text-amber-600/80 dark:text-amber-400/70">{{ t('bookings.statuses.pending') }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-blue-100/50 p-4 dark:border-blue-500/30 dark:from-blue-500/10 dark:to-blue-500/5">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-500/20 text-blue-600 dark:text-blue-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{{ statusCounts.confirmed }}</p>
            <p class="text-xs text-blue-600/80 dark:text-blue-400/70">{{ t('bookings.statuses.confirmed') }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-emerald-100/50 p-4 dark:border-emerald-500/30 dark:from-emerald-500/10 dark:to-emerald-500/5">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ statusCounts.completed }}</p>
            <p class="text-xs text-emerald-600/80 dark:text-emerald-400/70">{{ t('bookings.statuses.completed') }}</p>
          </div>
        </div>
      </div>
      <div class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-red-100/50 p-4 dark:border-red-500/30 dark:from-red-500/10 dark:to-red-500/5">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-500/20 text-red-600 dark:text-red-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </div>
          <div>
            <p class="text-2xl font-bold text-red-700 dark:text-red-400">{{ statusCounts.cancelled + statusCounts.expired }}</p>
            <p class="text-xs text-red-600/80 dark:text-red-400/70">{{ t('bookings.statuses.cancelled') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters & Actions -->
    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900/50 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex flex-wrap items-center gap-3">
        <div class="relative">
          <span class="absolute text-gray-400 -translate-y-1/2 left-3 top-1/2">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input
            v-model="search"
            type="text"
            :placeholder="t('datatable.searchPlaceholder')"
            class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-10 pr-4 text-sm text-gray-700 placeholder-gray-400 transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:placeholder-gray-500 dark:focus:border-brand-500 dark:focus:bg-gray-800 sm:w-64"
          />
        </div>

        <div class="relative">
          <select
            v-model="statusFilter"
            class="h-10 appearance-none rounded-lg border border-gray-200 bg-gray-50 py-2 pl-3 pr-10 text-sm text-gray-700 transition focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:focus:border-brand-500"
          >
            <option v-for="status in statuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
          <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </span>
        </div>

        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <span>{{ t('datatable.show') }}</span>
          <select
            v-model.number="perPage"
            class="h-8 appearance-none rounded-md border border-gray-200 bg-gray-50 px-2 text-sm text-gray-700 focus:border-brand-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
          >
            <option :value="10">10</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
          <span>{{ t('datatable.entries') }}</span>
        </div>
      </div>

      <Tooltip :text="t('messages.notAuthorized')" :show="!canCreate">
        <button
          :disabled="!canCreate"
          @click="handleCreateClick"
          class="inline-flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-brand-600 px-5 py-2.5 text-sm font-medium text-white shadow-lg shadow-brand-500/25 transition hover:from-brand-600 hover:to-brand-700 hover:shadow-brand-500/40 disabled:from-gray-400 disabled:to-gray-500 disabled:shadow-none"
        >
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ t('bookings.addBooking') }}
        </button>
      </Tooltip>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      <div
        v-for="booking in paginatedData"
        :key="booking.id"
        class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white transition-all duration-300 hover:border-gray-300 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900/50 dark:hover:border-gray-700"
      >
        <!-- Status Indicator Bar -->
        <div 
          class="absolute left-0 top-0 h-full w-1"
          :class="statusBarColor(booking.status)"
        ></div>

        <div class="p-5 ps-6">
          <!-- Header -->
          <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
              <!-- Avatar -->
              <div 
                class="flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold"
                :class="avatarColor(booking.status)"
              >
                {{ getInitials(booking.client_name) }}
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-white">
                  {{ booking.client_name || t('bookings.unknownClient') }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ t('bookings.bookingId') }}: #{{ booking.id }}
                </p>
              </div>
            </div>
            
            <!-- Status Badge -->
            <div class="flex items-center gap-2">
              <span 
                class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium"
                :class="statusBadgeClass(booking.status)"
              >
                <span class="h-1.5 w-1.5 rounded-full" :class="statusDotClass(booking.status)"></span>
                {{ t(`bookings.statuses.${booking.status}`) }}
              </span>
            </div>
          </div>

          <!-- Booking Details -->
          <div class="mt-4 grid grid-cols-2 gap-3">
            <!-- Date & Time -->
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-100 text-brand-600 dark:bg-brand-500/20 dark:text-brand-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('bookings.startAt') }}</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(booking.start_at) }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatTime(booking.start_at) }}</p>
              </div>
            </div>

            <!-- Duration -->
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('bookings.duration') }}</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.duration_minutes }} {{ t('common.minutes') }}</p>
              </div>
            </div>

            <!-- Consultant -->
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
              <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('bookings.consultant') }}</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ booking.consultant_name || '—' }}</p>
              </div>
            </div>

            <!-- Service Type with Dynamic Icon -->
            <div class="flex items-center gap-2 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
              <div 
                class="flex h-8 w-8 items-center justify-center rounded-lg"
                :class="getConsultationMethodStyle(booking).bgClass"
              >
                <!-- Video Icon -->
                <svg v-if="getConsultationMethod(booking) === 'video'" class="h-4 w-4" :class="getConsultationMethodStyle(booking).iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <!-- Audio Icon -->
                <svg v-else-if="getConsultationMethod(booking) === 'audio'" class="h-4 w-4" :class="getConsultationMethodStyle(booking).iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                <!-- Text/Chat Icon -->
                <svg v-else-if="getConsultationMethod(booking) === 'text'" class="h-4 w-4" :class="getConsultationMethodStyle(booking).iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <!-- Consultant Icon (default) -->
                <svg v-else class="h-4 w-4" :class="getConsultationMethodStyle(booking).iconClass" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('bookings.bookableType') }}</p>
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ getBookableLabel(booking.bookable_type) }}</p>
              </div>
            </div>
          </div>

          <!-- Notes (if exists) -->
          <div v-if="booking.notes" class="mt-3 rounded-lg bg-gray-50 p-3 dark:bg-gray-800/50">
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ t('bookings.notes') }}</p>
            <p class="mt-1 text-sm text-gray-700 dark:text-gray-300 line-clamp-2">{{ booking.notes }}</p>
          </div>

          <!-- Actions -->
          <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
            <Tooltip :text="t('messages.notAuthorized')" :show="!canView">
              <button 
                :disabled="!canView" 
                @click="handleViewClick(booking.id)" 
                class="inline-flex items-center gap-1.5 rounded-lg bg-gray-100 px-3 py-2 text-xs font-medium text-gray-700 transition hover:bg-gray-200 disabled:opacity-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                {{ t('common.view') }}
              </button>
            </Tooltip>
            
            <Tooltip :text="t('messages.notAuthorized')" :show="!canEdit">
              <button 
                :disabled="!canEdit" 
                @click="handleEditClick(booking.id)" 
                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-2 text-xs font-medium text-blue-600 transition hover:bg-blue-100 disabled:opacity-50 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                {{ t('common.edit') }}
              </button>
            </Tooltip>
            
            <Tooltip v-if="canCancelBooking(booking)" :text="t('messages.notAuthorized')" :show="!canEdit">
              <button 
                :disabled="!canEdit" 
                @click="handleCancelClick(booking)" 
                class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-2 text-xs font-medium text-orange-600 transition hover:bg-orange-100 disabled:opacity-50 dark:bg-orange-500/10 dark:text-orange-400 dark:hover:bg-orange-500/20"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
                {{ t('bookings.cancel') }}
              </button>
            </Tooltip>
            
            <Tooltip :text="t('messages.notAuthorized')" :show="!canDelete">
              <button 
                :disabled="!canDelete" 
                @click="handleDeleteClick(booking.id)" 
                class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100 disabled:opacity-50 dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20"
              >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                {{ t('common.delete') }}
              </button>
            </Tooltip>
          </div>
        </div>
      </div>
    </div>


    <!-- Empty state -->
    <div v-if="!paginatedData.length" class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50 px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-900/30">
      <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
        <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="mt-4 text-sm font-medium text-gray-900 dark:text-white">{{ t('bookings.noBookingsFound') }}</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ t('bookings.noBookingsDescription') }}</p>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900/50 sm:flex-row sm:items-center sm:justify-between">
      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ t('datatable.showing', { start: startEntry, end: endEntry, total: totalEntries }) }}
      </p>
      <div class="flex items-center gap-2">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1" 
          class="inline-flex h-9 items-center justify-center gap-1 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          {{ t('datatable.previous') }}
        </button>
        
        <div class="flex items-center gap-1">
          <template v-for="page in visiblePages" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              class="flex h-9 w-9 items-center justify-center rounded-lg text-sm font-medium transition"
              :class="page === currentPage 
                ? 'bg-brand-500 text-white' 
                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'"
            >
              {{ page }}
            </button>
            <span v-else class="px-1 text-gray-400">...</span>
          </template>
        </div>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages" 
          class="inline-flex h-9 items-center justify-center gap-1 rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          {{ t('datatable.next') }}
          <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Delete Modal -->
  <DangerAlert
    :isOpen="isDeleteModalOpen"
    :title="t('messages.areYouSure')"
    :message="t('bookings.deleteConfirmation')"
    @close="closeDeleteModal"
    @confirm="confirmDelete"
  />

  <!-- Cancel Modal -->
  <DangerAlert
    :isOpen="isCancelModalOpen"
    :title="t('messages.areYouSure')"
    :message="t('bookings.cancelConfirmation')"
    @close="closeCancelModal"
    @confirm="confirmCancel"
  />
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from '@/route'
import { useI18n } from 'vue-i18n'
import Tooltip from '@/components/ui/Tooltip.vue'
import DangerAlert from '@/components/modals/DangerAlert.vue'
import { usePermissions } from '@/composables/usePermissions'
import { useNotifications } from '@/composables/useNotifications'

const { hasAnyPermission } = usePermissions()
const canCreate = computed(() => hasAnyPermission(['bookings.create', 'bookings.store']))
const canView = computed(() => hasAnyPermission(['bookings.view', 'bookings.show']))
const canEdit = computed(() => hasAnyPermission(['bookings.update', 'bookings.edit']))
const canDelete = computed(() => hasAnyPermission(['bookings.delete', 'bookings.destroy']))

const { t } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
  bookings: Object,
  filters: Object,
  statusCounts: {
    type: Object,
    default: () => ({ pending: 0, confirmed: 0, completed: 0, cancelled: 0, expired: 0 })
  },
})

// Statuses
const statuses = computed(() => [
  { value: '', label: t('bookings.allStatuses') },
  { value: 'pending', label: t('bookings.statuses.pending') },
  { value: 'confirmed', label: t('bookings.statuses.confirmed') },
  { value: 'completed', label: t('bookings.statuses.completed') },
  { value: 'cancelled', label: t('bookings.statuses.cancelled') },
  { value: 'expired', label: t('bookings.statuses.expired') },
])

const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || '')
const currentPage = ref(props.bookings?.current_page ?? 1)
const perPage = ref(props.bookings?.per_page ?? 10)

const allData = computed(() => props.bookings?.data || [])

// Status counts from server (all bookings, not just current page)
const statusCounts = computed(() => props.statusCounts)

// Filter current page data client-side (server provides pagination metadata)
const filteredData = computed(() => {
  return (props.bookings?.data || [])
    .filter((b) => {
      const matchesStatus = !statusFilter.value || b.status === statusFilter.value
      const term = search.value?.toLowerCase() || ''
      const matchesSearch = !term ||
        b.client_name?.toLowerCase().includes(term) ||
        b.consultant_name?.toLowerCase().includes(term) ||
        String(b.id).includes(term) ||
        b.bookable_title?.toLowerCase().includes(term)
      return matchesStatus && matchesSearch
    })
})

// Use server-provided pagination metadata when available (like ShowAreas.vue)
const totalEntries = computed(() => props.bookings?.total ?? filteredData.value.length)
const totalPages = computed(() => props.bookings?.last_page ?? 1)
const startEntry = computed(() => props.bookings?.from ?? (filteredData.value.length ? (currentPage.value - 1) * perPage.value + 1 : 0))
const endEntry = computed(() => props.bookings?.to ?? Math.min(filteredData.value.length, currentPage.value * perPage.value))

// Paginated data shown in the list is the filtered results for the current server page
const paginatedData = computed(() => filteredData.value)

// Visible pages for pagination
const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    for (let i = 1; i <= total; i++) pages.push(i)
  } else {
    if (current <= 3) {
      pages.push(1, 2, 3, 4, '...', total)
    } else if (current >= total - 2) {
      pages.push(1, '...', total - 3, total - 2, total - 1, total)
    } else {
      pages.push(1, '...', current - 1, current, current + 1, '...', total)
    }
  }
  return pages
})

// Status styling functions
function statusBarColor(status) {
  const colors = {
    pending: 'bg-amber-500',
    confirmed: 'bg-blue-500',
    completed: 'bg-emerald-500',
    cancelled: 'bg-red-500',
    expired: 'bg-gray-400',
  }
  return colors[status] || 'bg-gray-400'
}

function avatarColor(status) {
  const colors = {
    pending: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
    confirmed: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
    expired: 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
  }
  return colors[status] || 'bg-gray-100 text-gray-700'
}

function statusBadgeClass(status) {
  const classes = {
    pending: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    confirmed: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
    completed: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    cancelled: 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    expired: 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
  }
  return classes[status] || 'bg-gray-100 text-gray-700'
}

function statusDotClass(status) {
  const classes = {
    pending: 'bg-amber-500',
    confirmed: 'bg-blue-500',
    completed: 'bg-emerald-500',
    cancelled: 'bg-red-500',
    expired: 'bg-gray-400',
  }
  return classes[status] || 'bg-gray-400'
}

function getInitials(name) {
  if (!name) return '?'
  return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()
}

function formatDate(dateTime) {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleDateString('ar-SA', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

function formatTime(dateTime) {
  if (!dateTime) return '-'
  return new Date(dateTime).toLocaleTimeString('ar-SA', {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function canCancelBooking(booking) {
  return ['pending', 'confirmed'].includes(booking.status)
}

function getBookableLabel(type) {
  if (!type) return '-'
  const labels = {
    Consultant: t('bookings.bookableTypes.consultant'),
    ConsultantService: t('bookings.bookableTypes.consultantService'),
  }
  return labels[type] || type
}

// Get consultation method based on booking type
function getConsultationMethod(booking) {
  if (booking.bookable_type === 'ConsultantService' && booking.consultation_method) {
    return booking.consultation_method
  }
  return 'consultant' // Default for direct consultant bookings
}

// Get styling for consultation method icon
function getConsultationMethodStyle(booking) {
  const method = getConsultationMethod(booking)
  const styles = {
    video: {
      bgClass: 'bg-blue-100 dark:bg-blue-500/20',
      iconClass: 'text-blue-600 dark:text-blue-400'
    },
    audio: {
      bgClass: 'bg-green-100 dark:bg-green-500/20',
      iconClass: 'text-green-600 dark:text-green-400'
    },
    text: {
      bgClass: 'bg-purple-100 dark:bg-purple-500/20',
      iconClass: 'text-purple-600 dark:text-purple-400'
    },
    consultant: {
      bgClass: 'bg-orange-100 dark:bg-orange-500/20',
      iconClass: 'text-orange-600 dark:text-orange-400'
    }
  }
  return styles[method] || styles.consultant
}

// Navigation
function handleCreateClick() {
  if (!canCreate.value) return
  router.visit(route('admin.bookings.create'))
}

function handleViewClick(id) {
  if (!canView.value) return
  router.visit(route('admin.bookings.show', id))
}

function handleEditClick(id) {
  if (!canEdit.value) return
  router.visit(route('admin.bookings.edit', id))
}

// Delete
const isDeleteModalOpen = ref(false)
const bookingToDeleteId = ref(null)

function handleDeleteClick(id) {
  if (!canDelete.value) return
  bookingToDeleteId.value = id
  isDeleteModalOpen.value = true
}

function closeDeleteModal() {
  isDeleteModalOpen.value = false
  bookingToDeleteId.value = null
}

function confirmDelete() {
  if (!bookingToDeleteId.value) return
  router.delete(route('admin.bookings.destroy', bookingToDeleteId.value), {
    onSuccess: () => {
      success(t('bookings.deletedSuccessfully'))
      closeDeleteModal()
    },
    onError: () => {
      error(t('bookings.deletionFailed'))
      closeDeleteModal()
    },
    preserveScroll: true,
  })
}

// Cancel
const isCancelModalOpen = ref(false)
const bookingToCancel = ref(null)

function handleCancelClick(booking) {
  if (!canEdit.value) return
  bookingToCancel.value = booking
  isCancelModalOpen.value = true
}

function closeCancelModal() {
  isCancelModalOpen.value = false
  bookingToCancel.value = null
}

function confirmCancel() {
  if (!bookingToCancel.value) return
  router.patch(route('admin.bookings.cancel', bookingToCancel.value.id), {}, {
    onSuccess: () => {
      success(t('bookings.cancelledSuccessfully'))
      closeCancelModal()
    },
    onError: () => {
      error(t('bookings.cancellationFailed'))
      closeCancelModal()
    },
    preserveScroll: true,
  })
}

// Pagination (server-driven)
const fetchPage = (page) => {
  const targetPage = page ?? currentPage.value
  router.get(window.location.pathname, {
    page: targetPage,
    per_page: perPage.value,
    search: search.value || undefined,
    status: statusFilter.value || undefined,
  }, { preserveState: true, preserveScroll: true, replace: true })
}

const nextPage = () => { if (currentPage.value < totalPages.value) fetchPage(currentPage.value + 1) }
const prevPage = () => { if (currentPage.value > 1) fetchPage(currentPage.value - 1) }
const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) fetchPage(page) }

// When filters or per-page change, reset to first page and fetch
watch([perPage, statusFilter, search], () => { fetchPage(1) })

// Sync local page/per_page when server returns different values
watch(() => props.bookings?.current_page, (val) => {
  currentPage.value = typeof val === 'number' ? val : 1
})
watch(() => props.bookings?.per_page, (val) => {
  if (typeof val === 'number') perPage.value = val
})
</script>
