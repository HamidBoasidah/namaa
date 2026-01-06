<template>
	<div class="space-y-6">
		<!-- Header Card -->
		<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
			<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
				<div class="space-y-1">
					<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
						{{ service.title || t('consultant_services.serviceName') }}
					</h2>
					<p class="text-sm text-gray-500 dark:text-gray-400">
						{{ consultantName }}
					</p>
				</div>

				<div class="flex flex-wrap items-center gap-3">
					<span
						class="inline-flex items-center justify-center gap-1 rounded-full px-3 py-1 text-xs font-medium"
						:class="{
							'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': service.is_active,
							'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !service.is_active,
						}"
					>
						{{ service.is_active ? t('common.active') : t('common.inactive') }}
					</span>

					<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
						{{ t('consultant_services.price') }}: {{ formattedPrice }}
					</span>

					<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
						{{ t('consultant_services.duration') }}: {{ formattedDuration }}
					</span>
				</div>
			</div>
		</div>

		<!-- Details Section -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h3 class="text-lg font-medium text-gray-800 dark:text-white">
					{{ t('consultant_services.showService') }}
				</h3>
			</div>

			<div class="p-4 sm:p-6">
				<div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.serviceName') }}
						</label>
						<p class="text-base text-gray-800 dark:text-white/90">
							{{ service.title || '—' }}
						</p>
					</div>

					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.consultantName') }}
						</label>
						<p class="text-base text-gray-800 dark:text-white/90">
							{{ consultantName }}
						</p>
					</div>

					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.category') }}
						</label>
						<p class="text-base text-gray-800 dark:text-white/90">
							{{ categoryName }}
						</p>
					</div>

					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.price') }}
						</label>
						<p class="text-base text-gray-800 dark:text-white/90">
							{{ formattedPrice }}
						</p>
					</div>

					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.duration') }}
						</label>
						<p class="text-base text-gray-800 dark:text-white/90">
							{{ formattedDuration }}
						</p>
					</div>

					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('common.status') }}
						</label>
						<span
							class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
							:class="{
								'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': service.is_active,
								'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !service.is_active,
							}"
						>
							{{ service.is_active ? t('common.active') : t('common.inactive') }}
						</span>
					</div>

					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.description') || t('consultant_services.serviceName') }}
						</label>
						<p class="whitespace-pre-line text-base text-gray-800 dark:text-white/90">
							{{ service.description || '—' }}
						</p>
					</div>

					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">
							{{ t('consultant_services.tags') }}
						</label>
						<div v-if="tagList.length" class="flex flex-wrap gap-2">
							<Badge
								v-for="tag in tagList"
								:key="tag.id || tag"
								variant="light"
								color="info"
								size="sm"
							>
								{{ displayTagLabel(tag) }}
							</Badge>
						</div>
							<p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ noTagsLabel }}</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Actions -->
		<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
			<Link
				:href="route('admin.consultant-services.index')"
				class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
			>
				{{ t('buttons.backToList') }}
			</Link>
			<Link
				:href="route('admin.consultant-services.edit', service.id)"
				class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
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
import Badge from '@/components/ui/Badge.vue'

const { t, locale } = useI18n()

const props = defineProps({
	service: { type: Object, required: true },
})

const service = computed(() => props.service || {})

const consultantName = computed(() => service.value?.consultant_display_name || service.value?.consultant_name || '—')
const categoryName = computed(() => {
	if (service.value?.category_name) return service.value.category_name
	const category = service.value?.category
	if (!category) return '—'
	return locale.value === 'ar' ? category.name_ar ?? category.name ?? '—' : category.name ?? category.name_ar ?? '—'
})

const tagList = computed(() => {
	const raw = service.value?.tags ?? service.value?.tag_list ?? []
	if (!Array.isArray(raw)) return []
	return raw
})

const noTagsLabel = computed(() => {
	const translation = t('tags.noTags')
	return translation === 'tags.noTags' ? '—' : translation
})

const formattedPrice = computed(() => {
	const price = service.value?.price
	if (price === null || price === undefined || price === '') return '—'
	const numericPrice = Number(price)
	if (Number.isNaN(numericPrice)) return price
	return numericPrice.toLocaleString(locale.value === 'ar' ? 'ar' : 'en', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
})

const formattedDuration = computed(() => {
	if (!service.value?.duration_minutes) return '—'
	return service.value.duration_minutes
})

function displayTagLabel(tag) {
	if (typeof tag === 'string' || typeof tag === 'number') return `#${tag}`
	return locale.value === 'ar' ? tag.name_ar ?? tag.name ?? `#${tag.id}` : tag.name ?? tag.name_ar ?? `#${tag.id}`
}
</script>
