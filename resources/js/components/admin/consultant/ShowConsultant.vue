<template>
  <div class="space-y-6">
    <!-- Header / Profile Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
      <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex flex-col items-center gap-6 xl:flex-row xl:items-center">
          <!-- Avatar -->
          <div class="relative">
            <div
              class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 dark:border-gray-800 flex items-center justify-center"
            >
              <img
                v-if="consultant?.avatar"
                :src="`/storage/${consultant.avatar}`"
                alt="Consultant"
                class="h-full w-full object-cover object-center"
              />
              <UserCircleIcon v-else class="h-20 w-20 text-gray-400" />
            </div>
          </div>

          <!-- Name + quick meta -->
          <div class="text-center xl:text-left">
            <h4 class="mb-1 text-lg font-semibold text-gray-800 dark:text-white/90">
              {{ consultant?.user_name || `#${consultant?.id ?? ''}` }}
            </h4>
          </div>
        </div>

        <!-- Badges + actions -->
        <div class="flex flex-col items-center gap-3 sm:flex-row xl:justify-end">
          <div class="flex items-center gap-2">
            <!-- Status badge -->
            <span
              class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
              :class="{
                'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': consultant?.is_active,
                'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !consultant?.is_active,
              }"
            >
              {{ consultant?.is_active ? t('common.active') : t('common.inactive') }}
            </span>

            <!-- Rating badge -->
            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
              ★ {{ ratingAvgText }}
              <span class="text-gray-500 dark:text-gray-400">({{ consultant?.ratings_count ?? 0 }})</span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabs Header -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800 sm:px-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.consultantInformation') }}
          </h2>

          <div
            class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-gray-900 sm:w-auto"
            role="tablist"
            aria-label="Consultant tabs"
          >
            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'info'"
              @click="activeTab = 'info'"
              class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
              :class="activeTab === 'info'
                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            >
              {{ t('consultants.consultantInformation') || 'Info' }}
            </button>

            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'hours'"
              @click="activeTab = 'hours'"
              class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
              :class="activeTab === 'hours'
                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            >
              {{ t('consultants.workingHours') || 'Working Hours' }}
            </button>

            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'experiences'"
              @click="activeTab = 'experiences'"
              class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
              :class="activeTab === 'experiences'
                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            >
              {{ t('consultants.experiences') || 'Experiences' }}
            </button>

            <button
              type="button"
              role="tab"
              :aria-selected="activeTab === 'holidays'"
              @click="activeTab = 'holidays'"
              class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
              :class="activeTab === 'holidays'
                ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
            >
              {{ t('consultants.holidays') || 'Holidays' }}
            </button>
          </div>
        </div>
      </div>

      <div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
        <span v-if="activeTab === 'info'">
          {{ t('consultants.updateInfoHint') || 'View consultant details, specialization and location.' }}
        </span>
        <span v-else-if="activeTab === 'hours'">
          {{ t('consultants.updateHoursHint') || 'View weekly working hours schedule.' }}
        </span>
        <span v-else-if="activeTab === 'experiences'">
          {{ t('consultants.updateExperiencesHint') || 'View consultant experiences list.' }}
        </span>
        <span v-else>
          {{ t('consultants.updateHolidaysHint') || 'View consultant holidays list.' }}
        </span>
      </div>
    </div>

    <!-- ========================= -->
    <!-- Tab: Info -->
    <!-- ========================= -->
    <div v-show="activeTab === 'info'" role="tabpanel" class="space-y-6">
      <!-- General Information -->
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.consultantInformation') }}
          </h2>
        </div>

        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ t('common.email') }}
              </label>
              <p class="text-base text-gray-800 dark:text-white/90">
                {{ consultant?.user_email || 'N/A' }}
              </p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ t('common.phoneNumber') }}
              </label>
              <p class="text-base text-gray-800 dark:text-white/90">
                {{ consultant?.user_phone || 'N/A' }}
              </p>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ t('common.status') }}
              </label>
              <span
                class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="{
                  'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': consultant?.is_active,
                  'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !consultant?.is_active,
                }"
              >
                {{ consultant?.is_active ? t('common.active') : t('common.inactive') }}
              </span>
            </div>


          </div>
        </div>
      </div>

      <!-- Experience & Specialization -->
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.experienceAndSpecialization') }}
          </h2>
        </div>

        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ t('consultants.yearsOfExperience') }}
              </label>
              <p class="text-base text-gray-800 dark:text-white/90">
                {{ consultant?.years_of_experience ?? 'N/A' }}
              </p>
            </div>

          </div>
        </div>
      </div>

    </div>

    <!-- ========================= -->
    <!-- Tab: Working Hours -->
    <!-- ========================= -->
    <div v-show="activeTab === 'hours'" role="tabpanel" class="space-y-6">
      <!-- Working Hours -->
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.workingHours') }}
          </h2>
        </div>

        <div class="p-4 sm:p-6">
          <div v-if="groupedWorkingHours.length" class="space-y-4">
            <div
              v-for="day in groupedWorkingHours"
              :key="day.day_of_week"
              class="rounded-xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900"
            >
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm font-semibold text-gray-800 dark:text-white/90">
                  {{ dayLabel(day.day_of_week) }}
                </div>

                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="slot in day.slots"
                    :key="slot.id"
                    class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium"
                    :class="slot.is_active
                      ? 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400'
                      : 'bg-gray-200 text-gray-700 dark:bg-white/10 dark:text-gray-300'
                    "
                  >
                    {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('consultants.noWorkingHours') }}
          </div>
        </div>
      </div>
    </div>

    <!-- ========================= -->
    <!-- Tab: Experiences -->
    <!-- ========================= -->
    <div v-show="activeTab === 'experiences'" role="tabpanel" class="space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.experiences') || 'Experiences' }}
          </h2>
        </div>

        <div class="p-4 sm:p-6">
          <div v-if="sortedExperiences.length" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="custom-scrollbar overflow-x-auto">
              <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                  <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">#</th>
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                      {{ t('consultants.experienceName') || 'Experience' }}
                    </th>
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
                      {{ t('common.status') || 'Status' }}
                    </th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                  <tr v-for="(exp, idx) in sortedExperiences" :key="exp._key">
                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
                    <td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                      {{ exp.name || '—' }}
                    </td>
                    <td class="px-5 py-4 text-sm whitespace-nowrap">
                      <span
                        class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="exp.is_active
                          ? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500'
                          : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'"
                      >
                        {{ exp.is_active ? (t('common.active') || 'Active') : (t('common.inactive') || 'Inactive') }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('consultants.noExperiences') || 'No experiences added.' }}
          </div>
        </div>
      </div>
    </div>

    <!-- ========================= -->
    <!-- Tab: Holidays -->
    <!-- ========================= -->
    <div v-show="activeTab === 'holidays'" role="tabpanel" class="space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
          <h2 class="text-lg font-medium text-gray-800 dark:text-white">
            {{ t('consultants.holidays') || 'Holidays' }}
          </h2>
        </div>

        <div class="p-4 sm:p-6">
          <div v-if="sortedHolidays.length" class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
            <div class="custom-scrollbar overflow-x-auto">
              <table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                  <tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">#</th>
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                      {{ t('consultants.holidayDate') || 'Holiday Date' }}
                    </th>
                    <th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                      {{ t('consultants.holidayName') || 'Holiday Name' }}
                    </th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
                  <tr v-for="(holiday, idx) in sortedHolidays" :key="holiday._key">
                    <td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
                    <td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                      {{ holiday.holiday_date }}
                    </td>
                    <td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
                      {{ holiday.name || '—' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            {{ t('consultants.noHolidays') || 'No holidays added.' }}
          </div>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link
        :href="route('admin.consultants.index')"
        class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
      >
        {{ t('buttons.backToList') }}
      </Link>
      <Link
        :href="route('admin.users.edit', consultant.id)"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
      >
        {{ t('buttons.edit') }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { route } from '@/route'
import { UserCircleIcon } from '@/icons'

const { t, locale } = useI18n()

// ✅ Tabs state (only addition)
const activeTab = ref('info')

const props = defineProps({
  consultant: Object,
})

const consultant = computed(() => props.consultant)

const governorateName = computed(() => {
  const c = props.consultant
  return locale.value === 'ar' ? c?.governorate_name_ar : c?.governorate_name_en
})

const districtName = computed(() => {
  const c = props.consultant
  return locale.value === 'ar' ? c?.district_name_ar : c?.district_name_en
})

const areaName = computed(() => {
  const c = props.consultant
  return locale.value === 'ar' ? c?.area_name_ar : c?.area_name_en
})

const rawWorkingHours = computed(() => {
  const c = props.consultant || {}
  // نعطي أولوية للمفعّل إن وُجد، وإلا نعرض الكل
  return (c.active_working_hours && c.active_working_hours.length)
    ? c.active_working_hours
    : (c.working_hours || [])
})

const groupedWorkingHours = computed(() => {
  const map = new Map()

  for (const wh of rawWorkingHours.value || []) {
    const day = Number(wh.day_of_week)
    if (!map.has(day)) map.set(day, [])
    map.get(day).push(wh)
  }

  return Array.from(map.entries())
    .sort((a, b) => a[0] - b[0])
    .map(([day_of_week, slots]) => ({
      day_of_week,
      slots: (slots || [])
        .slice()
        .sort((x, y) => String(x.start_time).localeCompare(String(y.start_time))),
    }))
})

function dayLabel(day) {
  const namesAr = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']
  const namesEn = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const idx = Number(day)
  return (locale.value === 'ar' ? namesAr : namesEn)[idx] ?? `Day ${idx}`
}

function formatTime(t) {
  if (!t) return ''
  return String(t).slice(0, 5) // HH:MM
}

const locationText = computed(() => {
  const parts = [governorateName.value, districtName.value, areaName.value].filter(Boolean)
  return parts.length ? parts.join(' - ') : 'N/A'
})

const ratingAvgText = computed(() => {
  const v = props.consultant?.rating_avg ?? 0
  return typeof v === 'number' ? v.toFixed(2) : String(v)
})

const sortedHolidays = computed(() => {
  const list = Array.isArray(props.consultant?.holidays) ? props.consultant.holidays : []
  return list
    .map(h => ({
      ...h,
      _key: `${h.id ?? 'holiday'}-${h.holiday_date ?? Math.random()}`,
    }))
    .sort((a, b) => String(a.holiday_date).localeCompare(String(b.holiday_date)))
})

const sortedExperiences = computed(() => {
  const list = Array.isArray(props.consultant?.experiences) ? props.consultant.experiences : []
  return list
    .map(exp => ({
      ...exp,
      _key: `${exp.id ?? 'exp'}-${exp.name ?? Math.random()}`,
    }))
    .sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')))
})
</script>