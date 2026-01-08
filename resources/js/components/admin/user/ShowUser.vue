<template>
  <div class="space-y-6">
    <!-- User Information Section -->
    <div
      class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
    >
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('users.userInformation') }}</h2>
      </div>
      <div class="p-4 sm:p-6">
        <div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
          <!-- First Name -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('profile.labels.firstName') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ user.first_name || 'N/A' }}</p>
          </div>

          <!-- Last Name -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('profile.labels.lastName') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ user.last_name || 'N/A' }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('common.email') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ user.email }}</p>
          </div>

          <!-- User Type -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('users.userType') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">
              {{ t('users.types.' + (user.user_type || 'customer')) }}
            </p>
          </div>

          <!-- Gender -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('kyc.gender') || 'Gender' }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">
              <span>{{ user.gender ? (t('kyc.genders.' + user.gender) || user.gender) : 'N/A' }}</span>
            </p>
          </div>

          <!-- Phone Number -->
          <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('common.phoneNumber') }}</label>
            <p class="text-base text-gray-800 dark:text-white/90">{{ user.phone_number || 'N/A' }}</p>
          </div>

            <!-- User Status -->
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('common.status') }}</label>
                <p class="text-base text-gray-800 dark:text-white/90">
                  <span
                    class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
                    :class="{
                      'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': user.is_active,
                      'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !user.is_active,
                    }"
                  >
                    {{ user.is_active ? t('common.active') : t('common.inactive') }}
                  </span>
                </p>
            </div>

        </div>
      </div>
    </div>

      <!-- User Image Section -->
    <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
        <h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('users.userImage') }}</h2>
      </div>
      <div class="p-4 sm:p-6">
        <div v-if="user.avatar" class="relative flex justify-center p-4">
          <img :src="`/storage/${user.avatar}`" alt="User Image" class="max-h-64 rounded-lg border border-gray-200 object-contain dark:border-gray-800" />
        </div>
        <div v-else class="flex justify-center p-10">
          <p class="text-center text-sm text-gray-500 dark:text-gray-400">
            {{ t('users.noImage') }}
          </p>
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
      <Link
        :href="route('admin.users.index')"
        class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
      >
        {{ t('buttons.backToList') }}
      </Link>
      <Link
        :href="route('admin.users.edit', user.id)"
        class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
      >
        {{ t('buttons.edit') }}
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()

const props = defineProps({
  user: Object,
});

function facilityDisplayName(facility) {
  if (!facility) return ''
  const loc = locale.value

  if (facility.display_name && typeof facility.display_name === 'object') {
    return facility.display_name[loc] ?? facility.display_name.en ?? facility.display_name.ar ?? ''
  }

  return facility[`name_${loc}`] ?? facility.name_en ?? facility.name_ar ?? ''
}

function displayName(role) {
  const loc = locale.value
  return role?.display_name?.[loc] ?? role?.display_name?.en ?? role?.name ?? ''
}
</script>
