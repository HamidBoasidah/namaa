<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('kyc.applicantSummary') }}</h2>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
          <div class="flex items-center gap-4">
            <div class="h-14 w-14">
              <img v-if="kyc.user?.avatar" :src="`/storage/${kyc.user.avatar}`" class="h-14 w-14 rounded-full object-cover" alt="" />
              <UserCircleIcon v-else class="h-14 w-14 text-gray-400" />
            </div>
            <div class="flex flex-col">
              <p class="text-base font-semibold text-gray-800 dark:text-white/90">{{ applicantName }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ kyc.user?.email ?? t('kyc.noEmail') }}</p>
            </div>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('common.phoneNumber') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ kyc.user?.phone_number ?? t('kyc.noPhone') }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.fullName') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ kyc.full_name || applicantName }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.gender') }}</label>
            <p class="text-base capitalize text-gray-800 dark:text-white/90">{{ genderLabel }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.dateOfBirth') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ displayDate(kyc.date_of_birth) }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.status') }}</label>
            <Badge size="sm" :color="statusBadgeColor">{{ statusLabel }}</Badge>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.verificationStatus') }}</label>
            <Badge size="sm" :color="verificationBadgeColor">{{ kyc.is_verified ? t('kyc.verified') : t('kyc.notVerified') }}</Badge>
          </div>
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.address') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ kyc.address || t('kyc.noAddressProvided') }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('kyc.documentDetails') }}</h2>
      </div>
      <div class="p-4 sm:p-6 space-y-4">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.documentType') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ documentTypeLabel }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.verifiedAt') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ displayDate(kyc.verified_at) }}</p>
          </div>
        </div>
        <div v-if="hasDocument" class="flex flex-col gap-3 rounded-lg border border-gray-200 bg-white px-4 py-4 dark:border-gray-700 dark:bg-gray-900 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ t('kyc.secureDocument') }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ documentFileName }}</p>
          </div>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              :aria-label="t('kyc.downloadDocument')"
              :disabled="!documentDownloadUrl"
              @click="handleDownloadDocument"
              class="shadow-theme-xs inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="21"
                height="20"
                viewBox="0 0 21 20"
                fill="none"
              >
                <path
                  d="M17.1661 13.333V15.4163C17.1661 16.1067 16.6064 16.6663 15.9161 16.6663H5.08203C4.39168 16.6663 3.83203 16.1067 3.83203 15.4163V13.333M10.5004 13.333L10.5004 3.33301M6.64456 9.47918L10.4986 13.3308L14.3529 9.47918"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
            <button
              type="button"
              :aria-label="t('kyc.viewDocument')"
              :disabled="!documentViewUrl"
              @click="handleViewDocument"
              class="shadow-theme-xs inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/[0.03] dark:hover:text-gray-200"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="21"
                height="20"
                viewBox="0 0 21 20"
                fill="none"
              >
                <path
                  d="M2.96487 10.7925C2.73306 10.2899 2.73306 9.71023 2.96487 9.20764C4.28084 6.35442 7.15966 4.375 10.4993 4.375C13.8389 4.375 16.7178 6.35442 18.0337 9.20765C18.2655 9.71024 18.2655 10.2899 18.0337 10.7925C16.7178 13.6458 13.8389 15.6252 10.4993 15.6252C7.15966 15.6252 4.28084 13.6458 2.96487 10.7925Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M13.5202 10C13.5202 11.6684 12.1677 13.0208 10.4993 13.0208C8.83099 13.0208 7.47852 11.6684 7.47852 10C7.47852 8.33164 8.83099 6.97917 10.4993 6.97917C12.1677 6.97917 13.5202 8.33164 13.5202 10Z"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
          </div>
        </div>
        <div v-else class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
          {{ t('kyc.noDocumentUploaded') }}
        </div>
        <div v-if="kyc.rejected_reason" class="rounded-lg border border-error-200 bg-error-50/40 px-4 py-3 text-sm text-error-700 dark:border-error-500/40 dark:bg-error-500/15 dark:text-error-200">
          <p class="font-medium">{{ t('kyc.rejectionReason') }}</p>
          <p>{{ kyc.rejected_reason }}</p>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link :href="route('admin.kycs.index')" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
        {{ t('buttons.backToList') }}
      </Link>
      <Link :href="route('admin.kycs.edit', kyc.id)" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition">
        {{ t('buttons.edit') }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { UserCircleIcon } from '@/icons'
import Badge from '@/components/ui/Badge.vue'
import { route } from '@/route'

const props = defineProps({
  kyc: {
    type: Object,
    required: true,
  },
})

const { t } = useI18n()

const kyc = computed(() => props.kyc ?? {})

const applicantName = computed(() => {
  if (kyc.value.full_name) return kyc.value.full_name
  const first = kyc.value.user?.first_name ?? ''
  const last = kyc.value.user?.last_name ?? ''
  const fallback = `${first} ${last}`.trim()
  return fallback || kyc.value.user?.name || t('kyc.unknownApplicant')
})

const genderLabel = computed(() => (kyc.value.gender ? t(`kyc.genders.${kyc.value.gender}`, kyc.value.gender) : '—'))

const statusBadgeColors = {
  pending: 'warning',
  approved: 'success',
  rejected: 'error',
}

const statusLabel = computed(() => t(`kyc.statuses.${kyc.value.status ?? 'pending'}`))
const statusBadgeColor = computed(() => statusBadgeColors[kyc.value.status] ?? 'warning')
const verificationBadgeColor = computed(() => (kyc.value.is_verified ? 'success' : 'warning'))

const documentTypeLabel = computed(() => (kyc.value.document_type ? t(`kyc.documentTypes.${kyc.value.document_type}`, kyc.value.document_type) : '—'))

const hasDocument = computed(() => !!kyc.value.document_scan_copy)
const documentFileName = computed(() => kyc.value.document_scan_copy?.split('/').pop() ?? t('kyc.noDocumentSelected'))
const documentViewUrl = computed(() => (hasDocument.value ? route('admin.kycs.document.view', kyc.value.id) : null))
const documentDownloadUrl = computed(() => (hasDocument.value ? route('admin.kycs.document.download', kyc.value.id) : null))

const displayDate = (value) => (value ? new Date(value).toLocaleDateString() : '—')

const handleDownloadDocument = () => {
  if (!documentDownloadUrl.value || typeof window === 'undefined') return
  window.open(documentDownloadUrl.value, '_blank', 'noopener')
}

const handleViewDocument = () => {
  if (!documentViewUrl.value || typeof window === 'undefined') return
  window.open(documentViewUrl.value, '_blank', 'noopener')
}
</script>
