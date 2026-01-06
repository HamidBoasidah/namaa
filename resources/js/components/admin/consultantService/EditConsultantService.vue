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
							{{ t('consultant_services.consultantName') }}
						</label>

						<div class="relative z-20 bg-transparent">
							<select
								v-model="form.consultant_id"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							>
								<option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ t('consultants.selectUserPlaceholder') }}
								</option>

								<option
									v-for="c in consultants"
									:key="c.id"
									:value="c.id"
									class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
								>
									{{ consultantLabel(c) }}
								</option>
							</select>

							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>

						<p v-if="form.errors.consultant_id" class="mt-1 text-sm text-error-500">
							{{ form.errors.consultant_id }}
						</p>
					</div>

					<!-- Category select -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.category') }}
						</label>

						<div class="relative z-20 bg-transparent">
							<select
								v-model="form.category_id"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							>
								<option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ t('consultant_services.selectCategory') }}
								</option>

								<option
									v-for="cat in categories"
									:key="cat.id"
									:value="cat.id"
									class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
								>
									{{ locale.value === 'ar' ? cat.name_ar ?? cat.name : cat.name }}
								</option>
							</select>

							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>

						<p v-if="form.errors.category_id" class="mt-1 text-sm text-error-500">
							{{ form.errors.category_id }}
						</p>
					</div>

					<!-- Title -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.serviceName') }}
						</label>
						<input
							v-model="form.title"
							type="text"
							autocomplete="off"
							:placeholder="t('consultant_services.serviceName')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
						<p v-if="form.errors.title" class="mt-1 text-sm text-error-500">{{ form.errors.title }}</p>
					</div>

					<!-- Price -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.price') }}
						</label>
						<input
							v-model="form.price"
							type="number"
							step="0.01"
							min="0"
							autocomplete="off"
							:placeholder="t('consultant_services.price')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
						<p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
					</div>

					<!-- Duration (minutes) as number input -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.duration') }}
						</label>
						<input
							v-model.number="form.duration_minutes"
							type="number"
							min="1"
							step="1"
							autocomplete="off"
							:placeholder="t('consultant_services.duration')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
						<p v-if="form.errors.duration_minutes" class="mt-1 text-sm text-error-500">{{ form.errors.duration_minutes }}</p>
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

					<!-- Description -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.description') || t('consultant_services.serviceName') }}
						</label>
						<textarea
							v-model="form.description"
							rows="4"
							:placeholder="t('consultant_services.description') || t('consultant_services.serviceName')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						></textarea>
						<p v-if="form.errors.description" class="mt-1 text-sm text-error-500">{{ form.errors.description }}</p>
					</div>

					<!-- Tags -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.tags') }}
						</label>
						<MultipleSelect
							v-model="selectedTags"
							:options="tagsOptions"
						/>
						<p v-if="form.errors.tags" class="mt-1 text-sm text-error-500">{{ form.errors.tags }}</p>
						<p v-if="form.errors['tags.*']" class="mt-1 text-sm text-error-500">{{ form.errors['tags.*'] }}</p>
					</div>
				</div>
			</div>

			<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
				<Link
					:href="route('admin.consultant-services.index')"
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
		</div>
	</form>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import { route } from '@/route'
import MultipleSelect from '@/components/ui/MultipleSelect.vue'

const { t, locale } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
	service: { type: Object, required: true },
	consultants: { type: Array, required: true },
	categories: { type: Array, required: false },
	tags: { type: Array, required: false },
})

const consultants = computed(() => props.consultants || [])
const categories = computed(() => props.categories || [])
const tags = computed(() => props.tags || [])

const initialTagIds = computed(() => {
	const raw = props.service?.tags ?? props.service?.tag_ids ?? []
	if (Array.isArray(raw) && raw.length && typeof raw[0] === 'object') {
		return raw.map(tag => tag.id)
	}
	return Array.isArray(raw) ? raw : []
})

const form = useForm({
	_method: 'PUT',
	consultant_id: props.service?.consultant_id ?? '',
	category_id: props.service?.category_id ?? '',
	title: props.service?.title ?? '',
	description: props.service?.description ?? '',
	price: props.service?.price ?? '',
	tags: initialTagIds.value,
	duration_minutes: props.service?.duration_minutes ?? 60,
	is_active: props.service?.is_active ?? true,
})

const tagsOptions = computed(() => (tags.value || []).map(tag => ({ value: tag.id, label: locale.value === 'ar' ? tag.name_ar ?? tag.name : tag.name })))
const selectedTags = computed({
	get() {
		return tagsOptions.value.filter(opt => (form.tags || []).includes(opt.value))
	},
	set(opts) {
		form.tags = opts.map(o => o.value)
	},
})

function consultantLabel(c) {
	const name = c?.display_name || c?.name || [c?.first_name, c?.last_name].filter(Boolean).join(' ')
	return name ? `${name}${c?.email ? ` — ${c.email}` : ''}` : (c?.email || `#${c?.id}`)
}

function update() {
	form.post(route('admin.consultant-services.update', props.service.id), {
		onSuccess: () => success(t('consultant_services.serviceUpdatedSuccessfully')),
		onError: () => error(t('consultant_services.serviceUpdateFailed')),
		preserveScroll: true,
	})
}
</script>
