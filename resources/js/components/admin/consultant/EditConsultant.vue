<template>
	<form class="space-y-6" @submit.prevent="update">
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

					<!-- email -->
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('common.email') }}
						</label>
						<input
							v-model="form.email"
							type="email"
							autocomplete="off"
							:placeholder="t('consultants.emailPlaceholder')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
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
		<ImageUploadBox v-model="form.profile_image" input-id="consultant-profile-image" label="consultants.profileImage" :initial-image="consultant.profile_image ? `/storage/${consultant.profile_image}` : null" />
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
				{{ form.processing ? t('common.loading') : t('buttons.update') }}
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
	consultant: { type: Object, required: true },
	users: { type: Array, required: true },
	governorates: { type: Array, required: true },
	districts: { type: Array, required: true },
	areas: { type: Array, required: true },
})

const users = computed(() => props.users || [])
const governorates = computed(() => props.governorates || [])

const consultant = props.consultant || {}

const form = useForm({
	user_id: consultant.user_id ?? '',
	display_name: consultant.display_name ?? '',
	bio: consultant.bio ?? '',
	email: consultant.email ?? '',
	phone: consultant.phone ?? '',
	years_of_experience: consultant.years_of_experience ?? '',
	specialization_summary: consultant.specialization_summary ?? '',
	profile_image: null,
	address: consultant.address ?? '',
	governorate_id: consultant.governorate_id ?? '',
	district_id: consultant.district_id ?? '',
	area_id: consultant.area_id ?? '',
	is_active: consultant.is_active ?? true,
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

function update() {
	form.patch(route('admin.consultants.update', consultant.id), {
		onSuccess: () => success(t('consultants.consultantUpdatedSuccessfully')),
		onError: () => error(t('consultants.consultantUpdateFailed')),
		preserveScroll: true,
	})
}
</script>

