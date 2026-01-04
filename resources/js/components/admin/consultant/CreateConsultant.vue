<template>
  <form class="space-y-6" @submit.prevent="create">
    <!-- Consultant Information -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">
          {{ t('consultants.consultantInformation') }}
        </h2>
      </div>

      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <!-- Select User -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.selectUser') }}
            </label>

            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.user_id"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              >
                <option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  {{ t('consultants.selectUserPlaceholder') }}
                </option>

                <option
                  v-for="u in users"
                  :key="u.id"
                  :value="u.id"
                  class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                  {{ userLabel(u) }}
                </option>
              </select>

              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>

            <p v-if="form.errors.user_id" class="mt-1 text-sm text-error-500">{{ form.errors.user_id }}</p>
          </div>

          <!-- display_name -->
          <div class="md:col-span-1">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.displayName') }}
            </label>
            <input
              v-model="form.display_name"
              type="text"
              autocomplete="off"
              :placeholder="t('consultants.displayNamePlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.display_name" class="mt-1 text-sm text-error-500">{{ form.errors.display_name }}</p>
          </div>

            <!-- Email Input -->
            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ t('common.email') }}
              </label>
              <div class="relative">
                <span
                  class="absolute left-0 top-1/2 -translate-y-1/2 border-r border-gray-200 px-3.5 py-3 text-gray-500 dark:border-gray-800 dark:text-gray-400"
                >
                  <svg
                    width="20"
                    height="20"
                    viewBox="0 0 20 20"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M3.04175 7.06206V14.375C3.04175 14.6511 3.26561 14.875 3.54175 14.875H16.4584C16.7346 14.875 16.9584 14.6511 16.9584 14.375V7.06245L11.1443 11.1168C10.457 11.5961 9.54373 11.5961 8.85638 11.1168L3.04175 7.06206ZM16.9584 5.19262C16.9584 5.19341 16.9584 5.1942 16.9584 5.19498V5.20026C16.9572 5.22216 16.946 5.24239 16.9279 5.25501L10.2864 9.88638C10.1145 10.0062 9.8862 10.0062 9.71437 9.88638L3.07255 5.25485C3.05342 5.24151 3.04202 5.21967 3.04202 5.19636C3.042 5.15695 3.07394 5.125 3.11335 5.125H16.8871C16.9253 5.125 16.9564 5.15494 16.9584 5.19262ZM18.4584 5.21428V14.375C18.4584 15.4796 17.563 16.375 16.4584 16.375H3.54175C2.43718 16.375 1.54175 15.4796 1.54175 14.375V5.19498C1.54175 5.1852 1.54194 5.17546 1.54231 5.16577C1.55858 4.31209 2.25571 3.625 3.11335 3.625H16.8871C17.7549 3.625 18.4584 4.32843 18.4585 5.19622C18.4585 5.20225 18.4585 5.20826 18.4584 5.21428Z"
                      fill="#667085"
                    />
                  </svg>
                </span>
                <input
                  v-model="form.email"
                  type="text"
                  autocomplete="new-email"
                  :placeholder="t('users.emailPlaceholder')"
                  class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pl-[62px] text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                />
              </div>
              <p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
            </div>

          <!-- phone -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.phone') }}
            </label>
            <input
              v-model="form.phone"
              type="text"
              autocomplete="off"
              :placeholder="t('consultants.phonePlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.phone" class="mt-1 text-sm text-error-500">{{ form.errors.phone }}</p>
          </div>

          <!-- years_of_experience -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.yearsOfExperience') }}
            </label>
            <input
              v-model="form.years_of_experience"
              type="number"
              min="0"
              step="1"
              autocomplete="off"
              :placeholder="t('consultants.yearsOfExperiencePlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.years_of_experience" class="mt-1 text-sm text-error-500">{{ form.errors.years_of_experience }}</p>
          </div>

          <!-- is_active -->
          <div class="flex items-end">
            <label class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
              <div class="relative">
                <input type="checkbox" class="sr-only" v-model="form.is_active" />
                <div class="block h-6 w-11 rounded-full" :class="form.is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                <div :class="form.is_active ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300 ease-linear"></div>
              </div>

              <span
                class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                :class="{
                  'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': form.is_active,
                  'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !form.is_active,
                }"
              >
                {{ form.is_active ? t('common.active') : t('common.inactive') }}
              </span>
            </label>
          </div>

          <!-- bio -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.bio') }}
            </label>
            <textarea
              v-model="form.bio"
              rows="4"
              :placeholder="t('consultants.bioPlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.bio" class="mt-1 text-sm text-error-500">{{ form.errors.bio }}</p>
          </div>

          <!-- specialization_summary -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('consultants.specializationSummary') }}
            </label>
            <textarea
              v-model="form.specialization_summary"
              rows="4"
              :placeholder="t('consultants.specializationSummaryPlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.specialization_summary" class="mt-1 text-sm text-error-500">{{ form.errors.specialization_summary }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Location -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">
          {{ t('consultants.locationInformation') }}
        </h2>
      </div>

      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <!-- address -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('common.address') }}
            </label>
            <input
              v-model="form.address"
              type="text"
              autocomplete="off"
              :placeholder="t('consultants.addressPlaceholder')"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.address" class="mt-1 text-sm text-error-500">{{ form.errors.address }}</p>
          </div>

          <!-- governorate -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('districts.selectGovernorate') }}
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.governorate_id"
                @change="resetAfterGovernorate()"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              >
                <option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  {{ t('districts.selectGovernorate') }}
                </option>
                <option
                  v-for="g in governorates"
                  :key="g.id"
                  :value="g.id"
                  class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                  {{ locale === 'ar' ? g.name_ar : g.name_en }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>
            <p v-if="form.errors.governorate_id" class="mt-1 text-sm text-error-500">{{ form.errors.governorate_id }}</p>
          </div>

          <!-- district -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('areas.selectDistrict') }}
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.district_id"
                @change="resetAfterDistrict()"
                :disabled="!form.governorate_id"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm"
                :class="[
                  !form.governorate_id ? 'text-gray-400 dark:text-white/30' : 'text-gray-800 dark:text-white/90',
                  'dark:border-gray-700 dark:bg-gray-900'
                ]"
              >
                <option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  {{ t('areas.selectDistrict') }}
                </option>

                <option
                  v-for="d in filteredDistricts"
                  :key="d.id"
                  :value="d.id"
                  class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                  {{ locale === 'ar' ? d.name_ar : d.name_en }}
                </option>
              </select>

              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>
            <p v-if="form.errors.district_id" class="mt-1 text-sm text-error-500">{{ form.errors.district_id }}</p>
          </div>

          <!-- area -->
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
              {{ t('areas.selectArea') }}
            </label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.area_id"
                :disabled="!form.district_id"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm"
                :class="[
                  !form.district_id ? 'text-gray-400 dark:text-white/30' : 'text-gray-800 dark:text-white/90',
                  'dark:border-gray-700 dark:bg-gray-900'
                ]"
              >
                <option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
                  {{ t('areas.selectArea') }}
                </option>

                <option
                  v-for="a in filteredAreas"
                  :key="a.id"
                  :value="a.id"
                  class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                  {{ locale === 'ar' ? a.name_ar : a.name_en }}
                </option>
              </select>

              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
                  <path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </span>
            </div>
            <p v-if="form.errors.area_id" class="mt-1 text-sm text-error-500">{{ form.errors.area_id }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Profile Image -->
    <ImageUploadBox v-model="form.profile_image" input-id="consultant-profile-image" label="consultants.profileImage" />
    <p v-if="form.errors.profile_image" class="mt-1 text-sm text-error-500">{{ form.errors.profile_image }}</p>

    <!-- Buttons -->
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link
        :href="route('admin.consultants.index')"
        class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
      >
        {{ t('buttons.backToList') }}
      </Link>

      <button
        type="submit"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
        :class="{ 'cursor-not-allowed opacity-70': form.processing }"
        :disabled="form.processing"
      >
        {{ form.processing ? t('common.loading') : t('buttons.create') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import ImageUploadBox from '@/Components/common/ImageUploadBox.vue'
import { route } from '@/route'

const { t, locale } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
  users: { type: Array, required: true },         // [{id, first_name, last_name, name, email}]
  governorates: { type: Array, required: true },  // [{id, name_ar, name_en}]
  districts: { type: Array, required: true },     // [{id, name_ar, name_en, governorate_id}]
  areas: { type: Array, required: true },         // [{id, name_ar, name_en, district_id}]
})

const users = computed(() => props.users || [])
const governorates = computed(() => props.governorates || [])

const form = useForm({
  user_id: '',
  display_name: '',
  bio: '',
  email: '',
  phone: '',
  years_of_experience: '',
  specialization_summary: '',
  profile_image: null,
  address: '',
  governorate_id: '',
  district_id: '',
  area_id: '',
  is_active: true,
})

const filteredDistricts = computed(() => {
  if (!form.governorate_id) return []
  return (props.districts || []).filter(d => String(d.governorate_id) === String(form.governorate_id))
})

const filteredAreas = computed(() => {
  if (!form.district_id) return []
  return (props.areas || []).filter(a => String(a.district_id) === String(form.district_id))
})

function resetAfterGovernorate() {
  form.district_id = ''
  form.area_id = ''
}

function resetAfterDistrict() {
  form.area_id = ''
}

function userLabel(u) {
  const name = u?.name || [u?.first_name, u?.last_name].filter(Boolean).join(' ')
  return name ? `${name}${u?.email ? ` — ${u.email}` : ''}` : (u?.email || `#${u?.id}`)
}

function create() {
  form.post(route('admin.consultants.store'), {
    onSuccess: () => success(t('consultants.consultantCreatedSuccessfully')),
    onError: () => error(t('consultants.consultantCreationFailed')),
    preserveScroll: true,
  })
}
</script>