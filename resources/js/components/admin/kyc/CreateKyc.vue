<template>
  <div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('kyc.applicantDetails') }}</h2>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.selectApplicant') }}</label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.user_id"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              >
                <option value="" disabled>{{ t('kyc.selectApplicantPlaceholder') }}</option>
                <option v-for="user in users" :key="user.id" :value="user.id" class="text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                  {{ userDisplay(user) }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
            </div>
            <p v-if="form.errors.user_id" class="mt-1 text-sm text-error-500">{{ form.errors.user_id }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.fullName') }}</label>
            <input
              v-model="form.full_name"
              type="text"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              :placeholder="t('kyc.fullNamePlaceholder')"
            />
            <p v-if="form.errors.full_name" class="mt-1 text-sm text-error-500">{{ form.errors.full_name }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.gender') }}</label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.gender"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              >
                <option value="male">{{ t('kyc.genders.male') }}</option>
                <option value="female">{{ t('kyc.genders.female') }}</option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
            </div>
            <p v-if="form.errors.gender" class="mt-1 text-sm text-error-500">{{ form.errors.gender }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.dateOfBirth') }}</label>
            <input
              v-model="form.date_of_birth"
              type="date"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            />
            <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-error-500">{{ form.errors.date_of_birth }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.address') }}</label>
            <textarea
              v-model="form.address"
              rows="3"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              :placeholder="t('kyc.addressPlaceholder')"
            ></textarea>
            <p v-if="form.errors.address" class="mt-1 text-sm text-error-500">{{ form.errors.address }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('kyc.documentInformation') }}</h2>
      </div>
      <div class="p-4 sm:p-6 space-y-5">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.documentType') }}</label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.document_type"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30"
              >
                <option value="" disabled>{{ t('kyc.selectDocumentType') }}</option>
                <option v-for="option in documentTypeOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
            </div>
            <p v-if="form.errors.document_type" class="mt-1 text-sm text-error-500">{{ form.errors.document_type }}</p>
          </div>
        </div>

        <div>
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.documentScan') }}</label>
          <label for="kyc-document" class="shadow-theme-xs group relative block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 transition hover:border-brand-500 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-500">
            <div v-if="!hasDocument" class="flex justify-center p-8">
              <div class="flex max-w-[260px] flex-col items-center gap-4 text-center">
                <div class="inline-flex h-13 w-13 items-center justify-center rounded-full border border-gray-200 text-gray-700 transition dark:border-gray-700 dark:text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M20.0004 16V18.5C20.0004 19.3284 19.3288 20 18.5004 20H5.49951C4.67108 20 3.99951 19.3284 3.99951 18.5V16M12.0015 4L12.0015 16M7.37454 8.6246L11.9994 4.00269L16.6245 8.6246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  <span class="font-medium text-gray-800 dark:text-white/90">{{ t('common.clickToUpload') }}</span>
                  {{ t('common.orDragDrop') }}
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ t('kyc.allowedExtensions') }}</p>
              </div>
            </div>
            <div v-else class="relative flex flex-col items-center gap-3 p-4">
              <div v-if="documentIsImage" class="w-full max-w-md">
                <img :src="documentPreviewUrl" class="max-h-64 w-full rounded-lg border border-gray-200 object-contain dark:border-gray-700" alt="Document preview" />
              </div>
              <div v-else class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 12h10M7 17h6" /></svg>
                <span>{{ selectedDocumentName }}</span>
              </div>
              <button type="button" @click.stop="removeDocument" class="rounded-full bg-error-500 px-3 py-1 text-xs font-medium text-white shadow-theme-xs">{{ t('kyc.removeDocument') }}</button>
            </div>
            <input ref="documentInput" type="file" id="kyc-document" class="hidden" accept="image/*,.pdf" @change="handleDocumentChange" />
          </label>
          <p v-if="form.errors.document_scan_copy" class="mt-1 text-sm text-error-500">{{ form.errors.document_scan_copy }}</p>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('kyc.statusAndVerification') }}</h2>
      </div>
      <div class="p-4 sm:p-6 space-y-6">
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.status') }}</label>
            <div class="relative z-20 bg-transparent">
              <select
                v-model="form.status"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              >
                <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
              <span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
                <svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" /></svg>
              </span>
            </div>
            <p v-if="form.errors.status" class="mt-1 text-sm text-error-500">{{ form.errors.status }}</p>
          </div>
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.verifiedAt') }}</label>
            <input
              v-model="form.verified_at"
              type="date"
              class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
              :disabled="!form.is_verified"
            />
            <p v-if="form.errors.verified_at" class="mt-1 text-sm text-error-500">{{ form.errors.verified_at }}</p>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.verificationStatus') }}</label>
            <label class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
              <div class="relative">
                <input type="checkbox" class="sr-only" v-model="form.is_verified" />
                <div class="block h-6 w-11 rounded-full" :class="form.is_verified ? 'bg-brand-500 dark:bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
                <div :class="form.is_verified ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300 ease-linear"></div>
              </div>
              <span class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium" :class="form.is_verified ? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300'">
                {{ form.is_verified ? t('kyc.verified') : t('kyc.notVerified') }}
              </span>
            </label>
          </div>
        </div>
        <div v-if="form.status === 'rejected'">
          <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('kyc.rejectionReason') }}</label>
          <textarea
            v-model="form.rejected_reason"
            rows="3"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
            :placeholder="t('kyc.rejectionReasonPlaceholder')"
          ></textarea>
          <p v-if="form.errors.rejected_reason" class="mt-1 text-sm text-error-500">{{ form.errors.rejected_reason }}</p>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link :href="route('admin.kycs.index')" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]">
        {{ t('buttons.backToList') }}
      </Link>
      <button
        type="button"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition disabled:bg-brand-300"
        :disabled="form.processing"
        @click="submit"
      >
        <span v-if="form.processing">{{ t('kyc.saving') }}</span>
        <span v-else>{{ t('buttons.create') }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { route } from '@/route'
import { useNotifications } from '@/composables/useNotifications'

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
})

const { t } = useI18n()
const { success, error } = useNotifications()

const form = useForm({
  user_id: '',
  full_name: '',
  gender: 'male',
  date_of_birth: '',
  address: '',
  document_type: '',
  document_scan_copy: null,
  status: 'pending',
  rejected_reason: '',
  is_verified: false,
  verified_at: '',
  
})

const documentPreviewUrl = ref(null)
const documentInput = ref(null)
const documentTypeOptions = computed(() => [
  { value: 'passport', label: t('kyc.documentTypes.passport') },
  { value: 'driving_license', label: t('kyc.documentTypes.driving_license') },
  { value: 'id_card', label: t('kyc.documentTypes.id_card') },
])

const statusOptions = computed(() => [
  { value: 'pending', label: t('kyc.statuses.pending') },
  { value: 'approved', label: t('kyc.statuses.approved') },
  { value: 'rejected', label: t('kyc.statuses.rejected') },
])

const hasDocument = computed(() => !!form.document_scan_copy)
const documentIsImage = computed(() => !!documentPreviewUrl.value)
const selectedDocumentName = computed(() => form.document_scan_copy?.name ?? t('kyc.noDocumentSelected'))

const revokePreview = () => {
  if (documentPreviewUrl.value && documentPreviewUrl.value.startsWith('blob:')) {
    URL.revokeObjectURL(documentPreviewUrl.value)
  }
  documentPreviewUrl.value = null
}

const handleDocumentChange = (event) => {
  const file = event.target.files?.[0]
  form.document_scan_copy = file || null
  revokePreview()
  if (file && file.type.startsWith('image/')) {
    documentPreviewUrl.value = URL.createObjectURL(file)
  } else {
    documentPreviewUrl.value = null
  }
}

const removeDocument = () => {
  form.document_scan_copy = null
  revokePreview()
  if (documentInput.value) documentInput.value.value = ''
}

const resetFormState = () => {
  removeDocument()
  form.reset()
}

const submit = () => {
  form.post(route('admin.kycs.store'), {
    onSuccess: () => {
      success(t('kyc.kycCreatedSuccessfully'))
      resetFormState()
    },
    onError: () => {
      error(t('kyc.kycCreationFailed'))
    },
    preserveScroll: true,
  })
}

watch(
  () => form.status,
  (status) => {
    if (status !== 'rejected') {
      form.rejected_reason = ''
    }
  },
)

watch(
  () => form.is_verified,
  (isVerified) => {
    if (!isVerified) {
      form.verified_at = ''
    }
  },
)

onBeforeUnmount(() => {
  revokePreview()
})

const userDisplay = (user) => {
  if (!user) return ''
  if (user.first_name || user.last_name) {
    return `${user.first_name ?? ''} ${user.last_name ?? ''}`.trim() || user.name || user.email
  }
  return user.name ?? user.email ?? `${t('kyc.user')} #${user.id}`
}
</script>
