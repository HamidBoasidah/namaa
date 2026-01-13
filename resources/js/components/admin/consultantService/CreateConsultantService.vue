<template>
	<form class="space-y-6" @submit.prevent="create">
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h2 class="text-lg font-medium text-gray-800 dark:text-white">
					{{ t('consultant_services.addService') }}
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
							<select
								v-model="form.consultant_id"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							>
								<option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ t('consultants.selectUserPlaceholder') }}
								</option>
								<option v-for="c in consultants" :key="c.id" :value="c.id" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ c.name }}
								</option>
							</select>
							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>
						<p v-if="form.errors.consultant_id" class="mt-1 text-sm text-error-500">{{ form.errors.consultant_id }}</p>
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
								<option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ t('consultant_services.selectCategory') }}
								</option>
								<option v-for="cat in categories" :key="cat.id" :value="cat.id" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">
									{{ cat.name }}
								</option>
							</select>
							<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
								<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
									<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</span>
						</div>
						<p v-if="form.errors.category_id" class="mt-1 text-sm text-error-500">{{ form.errors.category_id }}</p>
					</div>

					<!-- Title -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.serviceName') }} <span class="text-error-500">*</span>
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
							{{ t('consultant_services.price') }} <span class="text-error-500">*</span>
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

					<!-- Duration -->
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

					<!-- Buffer (minutes) -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.buffer') }}
						</label>
						<input
							v-model.number="form.buffer"
							type="number"
							min="0"
							step="1"
							autocomplete="off"
							:placeholder="t('consultant_services.bufferPlaceholder')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
						<p v-if="form.errors.buffer" class="mt-1 text-sm text-error-500">{{ form.errors.buffer }}</p>
					</div>

					<!-- Consultation Method -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.consultationMethod') }}
						</label>
						<div class="relative z-20 bg-transparent">
							<select
								v-model="form.consultation_method"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							>
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
					</div>

					<!-- Delivery Time -->
					<div class="md:col-span-1">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.deliveryTime') }}
						</label>
						<input
							v-model="form.delivery_time"
							type="text"
							autocomplete="off"
							:placeholder="t('consultant_services.deliveryTimePlaceholder')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						/>
					</div>

					<!-- Auto Accept & Active -->
					<div class="md:col-span-1 flex items-end gap-6">
						<label class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
							<div class="relative">
								<input type="checkbox" class="sr-only" v-model="form.auto_accept_requests" />
								<div class="block h-6 w-11 rounded-full" :class="form.auto_accept_requests ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
								<div :class="form.auto_accept_requests ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300 ease-linear"></div>
							</div>
							<span>{{ t('consultant_services.autoAccept') }}</span>
						</label>

						<label class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
							<div class="relative">
								<input type="checkbox" class="sr-only" v-model="form.is_active" />
								<div class="block h-6 w-11 rounded-full" :class="form.is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
								<div :class="form.is_active ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300 ease-linear"></div>
							</div>
							<span :class="form.is_active ? 'text-green-600' : 'text-error-600'">
								{{ form.is_active ? t('common.active') : t('common.inactive') }}
							</span>
						</label>
					</div>

					<!-- Description -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.description') }}
						</label>
						<textarea
							v-model="form.description"
							rows="3"
							:placeholder="t('consultant_services.description')"
							class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
						></textarea>
					</div>

					<!-- Tags -->
					<div class="md:col-span-2">
						<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
							{{ t('consultant_services.tags') }}
						</label>
						<MultipleSelect v-model="selectedTags" :options="tagsOptions" />
					</div>
				</div>
			</div>
		</div>

		<!-- Service Details Section -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h2 class="text-lg font-medium text-gray-800 dark:text-white">
					{{ t('consultant_services.serviceDetails') }}
				</h2>
			</div>

			<div class="p-4 sm:p-6 space-y-6">
				<!-- ماذا تشمل الخدمة -->
				<DynamicListInput
					v-model="form.includes"
					:label="t('consultant_services.includes')"
					:placeholder="t('consultant_services.includesPlaceholder')"
				/>

				<!-- لمن هذه الخدمة -->
				<DynamicListInput
					v-model="form.target_audience"
					:label="t('consultant_services.targetAudience')"
					:placeholder="t('consultant_services.targetAudiencePlaceholder')"
				/>

				<!-- ما الذي يستلمه العميل -->
				<DynamicListInput
					v-model="form.deliverables"
					:label="t('consultant_services.deliverables')"
					:placeholder="t('consultant_services.deliverablesPlaceholder')"
				/>
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
import { route } from '@/route'
import MultipleSelect from '@/components/ui/MultipleSelect.vue'
import DynamicListInput from '@/components/ui/DynamicListInput.vue'

const { t, locale } = useI18n()
const { success, error } = useNotifications()

const props = defineProps({
	consultants: { type: Array, required: true },
	categories: { type: Array, default: () => [] },
	tags: { type: Array, default: () => [] },
})

const tagsOptions = computed(() => (props.tags || []).map(tag => ({ 
	value: tag.id, 
	label: tag.name 
})))

const selectedTags = computed({
	get() {
		return tagsOptions.value.filter(opt => form.tags.includes(opt.value))
	},
	set(opts) {
		form.tags = opts.map(o => o.value)
	},
})

const form = useForm({
	consultant_id: '',
	category_id: '',
	title: '',
	description: '',
	price: '',
	tags: [],
	duration_minutes: 60,
	buffer: 0,
	consultation_method: 'video',
	delivery_time: '',
	auto_accept_requests: false,
	is_active: true,
	includes: [],
	target_audience: [],
	deliverables: [],
})

function create() {
	form.post(route('admin.consultant-services.store'), {
		onSuccess: () => success(t('consultant_services.serviceCreatedSuccessfully')),
		onError: () => error(t('consultant_services.serviceCreationFailed')),
		preserveScroll: true,
	})
}
</script>
