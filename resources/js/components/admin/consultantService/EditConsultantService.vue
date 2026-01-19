<template>
	<form class="space-y-6" @submit.prevent="update">
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h2 class="text-lg font-medium text-gray-800 dark:text-white">
					{{ t('consultant_services.editService') }}
				</h2>
			</div>

			<div class="p-4 sm:p-6">
				<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
					<!-- Consultant select -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.consultantName') }} <span class="text-error-500">*</span>
						</label>
						<div class="relative z-20 bg-transparent">
							<select v-model="form.consultant_id" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
								<option value="" disabled>{{ t('consultants.selectUserPlaceholder') }}</option>
								<option v-for="c in consultants" :key="c.id" :value="c.id">{{ c.name }}</option>
							</select>
							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>
						<p v-if="form.errors.consultant_id" class="mt-1 text-sm text-error-500">{{ form.errors.consultant_id }}</p>
					</div>

					<!-- Category -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.category') }}</label>
						<div class="relative z-20 bg-transparent">
							<select v-model="form.category_id" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
								<option value="">{{ t('consultant_services.selectCategory') }}</option>
								<option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
							</select>
							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>
					</div>

					<!-- Title -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.serviceName') }} <span class="text-error-500">*</span></label>
						<input v-model="form.title" type="text" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
						<p v-if="form.errors.title" class="mt-1 text-sm text-error-500">{{ form.errors.title }}</p>
					</div>

					<!-- Price -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.price') }} <span class="text-error-500">*</span></label>
						<input v-model="form.price" type="number" step="0.01" min="0" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
						<p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
					</div>

					<!-- Duration -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.duration') }}</label>
						<input v-model.number="form.duration_minutes" type="number" min="1" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
					</div>

					<!-- Buffer (minutes) -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.buffer') }}</label>
						<input v-model.number="form.buffer" type="number" min="0" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
						<p v-if="form.errors.buffer" class="mt-1 text-sm text-error-500">{{ form.errors.buffer }}</p>
					</div>

					<!-- Consultation Method -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.consultationMethod') }}</label>
						<select v-model="form.consultation_method" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
							<option value="video">{{ t('consultant_services.methods.video') }}</option>
							<option value="audio">{{ t('consultant_services.methods.audio') }}</option>
							<option value="text">{{ t('consultant_services.methods.text') }}</option>
						</select>
						<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
						</span>
					</div>

					<!-- Delivery Time -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.deliveryTime') }}</label>
						<input v-model="form.delivery_time" type="text" class="dark:bg-dark-900 shadow-theme-xs h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
					</div>

					<!-- Toggles -->
					<div class="md:col-span-1 flex items-end gap-6">
						<label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
							<div class="relative">
								<input type="checkbox" class="sr-only" v-model="form.auto_accept_requests" />
								<div class="block h-6 w-11 rounded-full" :class="form.auto_accept_requests ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
								<div :class="form.auto_accept_requests ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300"></div>
							</div>
							<span>{{ t('consultant_services.autoAccept') }}</span>
						</label>
						<label class="flex cursor-pointer items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
							<div class="relative">
								<input type="checkbox" class="sr-only" v-model="form.is_active" />
								<div class="block h-6 w-11 rounded-full" :class="form.is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
								<div :class="form.is_active ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300"></div>
							</div>
							<span :class="form.is_active ? 'text-green-600' : 'text-error-600'">{{ form.is_active ? t('common.active') : t('common.inactive') }}</span>
						</label>
					</div>

					<!-- Description -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.description') }}</label>
						<textarea v-model="form.description" rows="3" class="dark:bg-dark-900 shadow-theme-xs w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
					</div>

					<!-- Icon Upload/Management -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.icon') }}
						</label>
						
						<!-- Current Icon Display -->
						<div v-if="currentIconUrl" class="mb-4">
							<p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ t('consultant_services.currentIcon') }}</p>
							<div class="flex items-center gap-4">
								<div class="flex h-20 w-20 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
									<img :src="currentIconUrl" alt="Service icon" class="max-h-16 max-w-16 object-contain" />
								</div>
								<button 
									type="button"
									@click="removeIcon" 
									class="inline-flex items-center gap-2 rounded-lg bg-error-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-error-600"
								>
									<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
										<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
									</svg>
									{{ t('consultant_services.removeIcon') }}
								</button>
							</div>
						</div>
						
						<!-- Upload New Icon -->
						<div class="relative">
							<label
								for="service-icon-edit"
								class="shadow-theme-xs group relative block cursor-pointer rounded-lg border-2 border-dashed border-gray-300 transition hover:border-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:hover:border-brand-500"
								:class="{ 'border-error-500 dark:border-error-500': iconError || form.errors.icon }"
							>
								<div class="flex justify-center p-6">
									<div class="flex max-w-[260px] flex-col items-center gap-3">
										<div class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-gray-200 text-gray-700 transition dark:border-gray-700 dark:text-gray-400">
											<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
												<path d="M20.0004 16V18.5C20.0004 19.3284 19.3288 20 18.5004 20H5.49951C4.67108 20 3.99951 19.3284 3.99951 18.5V16M12.0015 4L12.0015 16M7.37454 8.6246L11.9994 4.00269L16.6245 8.6246" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
											</svg>
										</div>
										<div class="text-center">
											<p class="text-sm text-gray-500 dark:text-gray-400">
												<span class="font-medium text-gray-800 dark:text-white/90">
													{{ currentIconUrl ? t('consultant_services.changeIcon') : t('consultant_services.clickToUploadIcon') }}
												</span>
											</p>
											<p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
												{{ t('consultant_services.iconRequirements') }}
											</p>
										</div>
									</div>
								</div>
								
								<input 
									ref="iconInput"
									id="service-icon-edit" 
									type="file" 
									class="hidden" 
									accept="image/jpeg,image/png,image/gif,image/svg+xml,image/webp"
									@change="handleIconChange" 
								/>
							</label>
						</div>
						<p v-if="iconError" class="mt-1 text-sm text-error-500">{{ iconError }}</p>
						<p v-else-if="form.errors.icon" class="mt-1 text-sm text-error-500">{{ form.errors.icon }}</p>
						<p v-else-if="form.errors.remove_icon" class="mt-1 text-sm text-error-500">{{ form.errors.remove_icon }}</p>
					</div>

					<!-- Tags -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.tags') }}</label>
						<MultipleSelect v-model="selectedTags" :options="tagsOptions" />
					</div>
				</div>
			</div>
		</div>

		<!-- Service Details -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h2 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('consultant_services.serviceDetails') }}</h2>
			</div>
			<div class="p-4 sm:p-6 space-y-6">
				<DynamicListInput v-model="form.includes" :label="t('consultant_services.includes')" :placeholder="t('consultant_services.includesPlaceholder')" />
				<DynamicListInput v-model="form.target_audience" :label="t('consultant_services.targetAudience')" :placeholder="t('consultant_services.targetAudiencePlaceholder')" />
				<DynamicListInput v-model="form.deliverables" :label="t('consultant_services.deliverables')" :placeholder="t('consultant_services.deliverablesPlaceholder')" />
			</div>
		</div>

		<!-- Actions -->
		<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
			<Link :href="route('admin.consultant-services.index')" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">{{ t('buttons.backToList') }}</Link>
			<button type="submit" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white" :disabled="form.processing">{{ form.processing ? t('common.loading') : t('buttons.update') }}</button>
		</div>
	</form>
</template>

<script setup>
import { computed, ref, onBeforeUnmount } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import { route } from '@/route'
import MultipleSelect from '@/components/ui/MultipleSelect.vue'
import DynamicListInput from '@/components/ui/DynamicListInput.vue'

const { t } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
	service: { type: Object, required: true },
	consultants: { type: Array, required: true },
	categories: { type: Array, default: () => [] },
	tags: { type: Array, default: () => [] },
})

const tagsOptions = computed(() => (props.tags || []).map(tag => ({ value: tag.id, label: tag.name })))
const initialTagIds = computed(() => {
	const raw = props.service?.tags ?? []
	return Array.isArray(raw) && raw.length && typeof raw[0] === 'object' ? raw.map(tag => tag.id) : (Array.isArray(raw) ? raw : [])
})

// Icon upload state
const iconInput = ref(null)
const iconPreviewUrl = ref(null)
const iconError = ref(null)

// Allowed file types and max size
const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp']
const maxFileSize = 2 * 1024 * 1024 // 2MB in bytes

const form = useForm({
	_method: 'PUT',
	consultant_id: props.service?.consultant_id ?? '',
	category_id: props.service?.category_id ?? '',
	title: props.service?.title ?? '',
	description: props.service?.description ?? '',
	price: props.service?.price ?? '',
	tags: initialTagIds.value,
	duration_minutes: props.service?.duration_minutes ?? 60,
	buffer: props.service?.buffer ?? 0,
	consultation_method: props.service?.consultation_method ?? 'video',
	delivery_time: props.service?.delivery_time ?? '',
	auto_accept_requests: props.service?.auto_accept_requests ?? false,
	is_active: props.service?.is_active ?? true,
	includes: props.service?.includes ?? [],
	target_audience: props.service?.target_audience ?? [],
	deliverables: props.service?.deliverables ?? [],
	icon: null,
	remove_icon: false,
})

const selectedTags = computed({
	get() { return tagsOptions.value.filter(opt => (form.tags || []).includes(opt.value)) },
	set(opts) { form.tags = opts.map(o => o.value) },
})

/**
 * Computed property to show current icon URL
 * Returns null if remove_icon is true, otherwise returns preview or existing icon
 */
const currentIconUrl = computed(() => {
	if (form.remove_icon) return null
	return iconPreviewUrl.value || props.service?.icon_url
})

/**
 * Validate the selected icon file
 */
function validateIconFile(file) {
	iconError.value = null
	
	if (!file) return true
	
	// Check file type
	if (!allowedTypes.includes(file.type)) {
		iconError.value = t('consultant_services.iconInvalidType')
		return false
	}
	
	// Check file size
	if (file.size > maxFileSize) {
		iconError.value = t('consultant_services.iconTooLarge')
		return false
	}
	
	return true
}

/**
 * Handle icon file selection
 */
function handleIconChange(event) {
	const file = event.target.files?.[0] || null
	
	// Clear previous preview
	if (iconPreviewUrl.value) {
		URL.revokeObjectURL(iconPreviewUrl.value)
		iconPreviewUrl.value = null
	}
	
	if (!file) {
		form.icon = null
		return
	}
	
	// Validate the file
	if (!validateIconFile(file)) {
		// Reset the input
		if (iconInput.value) iconInput.value.value = ''
		form.icon = null
		return
	}
	
	// Set the file and create preview
	form.icon = file
	form.remove_icon = false // Reset remove flag when uploading new icon
	iconPreviewUrl.value = URL.createObjectURL(file)
}

/**
 * Remove the icon (either new preview or existing icon)
 */
function removeIcon() {
	// Clear preview URL if exists
	if (iconPreviewUrl.value) {
		URL.revokeObjectURL(iconPreviewUrl.value)
		iconPreviewUrl.value = null
	}
	
	form.icon = null
	iconError.value = null
	
	// Reset file input
	if (iconInput.value) iconInput.value.value = ''
	
	// If there was an existing icon, set remove_icon flag
	if (props.service?.icon_url) {
		form.remove_icon = true
	}
}

// Cleanup on unmount
onBeforeUnmount(() => {
	if (iconPreviewUrl.value) {
		URL.revokeObjectURL(iconPreviewUrl.value)
	}
})

function update() {
	form.post(route('admin.consultant-services.update', props.service.id), {
		onSuccess: () => success(t('consultant_services.serviceUpdatedSuccessfully')),
		onError: () => error(t('consultant_services.serviceUpdateFailed')),
		preserveScroll: true,
	})
}
</script>
