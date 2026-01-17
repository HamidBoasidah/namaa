<template>
	<div class="space-y-6">
		<!-- Header Card -->
		<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">
			<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
				<div class="space-y-1">
					<h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ service.title || t('consultant_services.serviceName') }}</h2>
					<p class="text-sm text-gray-500 dark:text-gray-400">{{ service.consultant_display_name || '-' }}</p>
				</div>
				<div class="flex flex-wrap items-center gap-3">
					<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium" :class="service.is_active ? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500' : 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'">
						{{ service.is_active ? t('common.active') : t('common.inactive') }}
					</span>
					<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
						{{ t('consultant_services.price') }}: {{ formatPrice(service.price) }}
					</span>
					<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
						{{ t('consultant_services.duration') }}: {{ service.duration_minutes }} {{ t('common.minutes') }}
					</span>
					<span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
						{{ t('consultant_services.buffer') }}: {{ service.buffer }} {{ t('common.minutes') }}
					</span>
				</div>
			</div>
		</div>

		<!-- Basic Info -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('consultant_services.basicInfo') }}</h3>
			</div>
			<div class="p-4 sm:p-6">
				<div class="grid grid-cols-1 gap-x-5 gap-y-6 md:grid-cols-2">
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.serviceName') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.title || '—' }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.consultantName') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.consultant_display_name || '—' }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.category') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.category_name || '—' }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.price') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ formatPrice(service.price) }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.duration') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.duration_minutes }} {{ t('common.minutes') }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.buffer') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.buffer }} {{ t('common.minutes') }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.consultationMethod') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ t(`consultant_services.methods.${service.consultation_method}`) }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.deliveryTime') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.delivery_time || '—' }}</p>
					</div>
					<div>
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.autoAccept') }}</label>
						<p class="text-base text-gray-800 dark:text-white/90">{{ service.auto_accept_requests ? t('common.yes') : t('common.no') }}</p>
					</div>
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.description') }}</label>
						<p class="whitespace-pre-line text-base text-gray-800 dark:text-white/90">{{ service.description || '—' }}</p>
					</div>
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-500 dark:text-gray-400">{{ t('consultant_services.tags') }}</label>
						<div v-if="tagList.length" class="flex flex-wrap gap-2">
							<Badge v-for="tag in tagList" :key="tag.id || tag" variant="light" color="info" size="sm">{{ displayTagLabel(tag) }}</Badge>
						</div>
						<p v-else class="text-sm text-gray-500 dark:text-gray-400">—</p>
					</div>
				</div>
			</div>
		</div>

		<!-- Service Details -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('consultant_services.serviceDetails') }}</h3>
			</div>
			<div class="p-4 sm:p-6 space-y-6">
				<!-- ماذا تشمل الخدمة -->
				<div>
					<label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.includes') }}</label>
					<ul v-if="service.includes?.length" class="list-disc list-inside space-y-1 text-gray-800 dark:text-white/90">
						<li v-for="(item, i) in service.includes" :key="i">{{ item }}</li>
					</ul>
					<p v-else class="text-sm text-gray-500 dark:text-gray-400">—</p>
				</div>

				<!-- لمن هذه الخدمة -->
				<div>
					<label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.targetAudience') }}</label>
					<ul v-if="service.target_audience?.length" class="list-disc list-inside space-y-1 text-gray-800 dark:text-white/90">
						<li v-for="(item, i) in service.target_audience" :key="i">{{ item }}</li>
					</ul>
					<p v-else class="text-sm text-gray-500 dark:text-gray-400">—</p>
				</div>

				<!-- ما الذي يستلمه العميل -->
				<div>
					<label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-400">{{ t('consultant_services.deliverables') }}</label>
					<ul v-if="service.deliverables?.length" class="list-disc list-inside space-y-1 text-gray-800 dark:text-white/90">
						<li v-for="(item, i) in service.deliverables" :key="i">{{ item }}</li>
					</ul>
					<p v-else class="text-sm text-gray-500 dark:text-gray-400">—</p>
				</div>
			</div>
		</div>

		<!-- Actions -->
		<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
			<Link :href="route('admin.consultant-services.index')" class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">{{ t('buttons.backToList') }}</Link>
			<Link :href="route('admin.consultant-services.edit', service.id)" class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white">{{ t('buttons.edit') }}</Link>
		</div>

		<!-- Reviews Section -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h3 class="text-lg font-medium text-gray-800 dark:text-white">{{ t('reviews.title') || 'Reviews' }}</h3>
			</div>
			<div class="p-4 sm:p-6">
				<ReviewsSection
					entity-type="service"
					:entity-id="service?.id"
				/>
			</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { route } from '@/route'
import Badge from '@/components/ui/Badge.vue'
import { ReviewsSection } from '@/components/reviews'

const { t, locale } = useI18n()

const props = defineProps({
	service: { type: Object, required: true },
})

const service = computed(() => props.service || {})
const tagList = computed(() => service.value?.tags ?? [])

function formatPrice(price) {
	if (price == null) return '—'
	const num = Number(price)
	return Number.isNaN(num) ? price : num.toLocaleString(locale.value === 'ar' ? 'ar' : 'en', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function displayTagLabel(tag) {
	if (typeof tag === 'string' || typeof tag === 'number') return `#${tag}`
	return tag.name ?? `#${tag.id}`
}
</script>
