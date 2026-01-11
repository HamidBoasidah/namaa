<template>
	<div>
		<label v-if="label" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
			{{ label }}
		</label>
		
		<div class="space-y-2">
			<div v-for="(item, index) in modelValue" :key="index" class="flex items-center gap-2">
				<input
					:value="item"
					@input="updateItem(index, $event.target.value)"
					type="text"
					:placeholder="placeholder"
					class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
				/>
				<button
					type="button"
					@click="removeItem(index)"
					class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-gray-300 text-gray-500 hover:border-error-300 hover:bg-error-50 hover:text-error-500 dark:border-gray-700 dark:hover:border-error-700 dark:hover:bg-error-500/10"
				>
					<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
			
			<!-- Add new item -->
			<div class="flex items-center gap-2">
				<input
					v-model="newItem"
					type="text"
					:placeholder="placeholder"
					@keyup.enter="addItem"
					class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
				/>
				<button
					type="button"
					@click="addItem"
					:disabled="!newItem.trim()"
					class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-brand-300 bg-brand-50 text-brand-500 hover:bg-brand-100 disabled:opacity-50 disabled:cursor-not-allowed dark:border-brand-700 dark:bg-brand-500/10 dark:hover:bg-brand-500/20"
				>
					<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
					</svg>
				</button>
			</div>
		</div>
	</div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
	modelValue: {
		type: Array,
		default: () => []
	},
	label: {
		type: String,
		default: ''
	},
	placeholder: {
		type: String,
		default: ''
	}
})

const emit = defineEmits(['update:modelValue'])

const newItem = ref('')

function addItem() {
	if (newItem.value.trim()) {
		emit('update:modelValue', [...props.modelValue, newItem.value.trim()])
		newItem.value = ''
	}
}

function removeItem(index) {
	const updated = [...props.modelValue]
	updated.splice(index, 1)
	emit('update:modelValue', updated)
}

function updateItem(index, value) {
	const updated = [...props.modelValue]
	updated[index] = value
	emit('update:modelValue', updated)
}
</script>
