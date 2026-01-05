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

		<!-- ✅ Working Hours (Weekly) -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
				<h2 class="text-lg font-medium text-gray-800 dark:text-white">
					{{ t('consultants.workingHours') || 'Working Hours' }}
				</h2>
			</div>

			<div class="p-4 sm:p-6 space-y-6">
				<!-- Global error -->
				<p v-if="workingHoursError" class="text-sm text-error-500">{{ workingHoursError }}</p>

				<!-- Days -->
				<div
					v-for="day in daysOfWeek"
					:key="day.value"
					class="rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900"
				>
					<div class="flex items-center justify-between gap-3">
						<h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">
							{{ day.label }}
						</h3>

						<!-- Optional: show count -->
						<span class="text-xs text-gray-500 dark:text-gray-400">
							{{ (week[day.value]?.length || 0) }} {{ t('common.items') || 'items' }}
						</span>
					</div>

					<!-- Table -->
					<div class="mt-4 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
						<div class="custom-scrollbar overflow-x-auto">
							<table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
								<thead class="bg-gray-50 dark:bg-gray-900">
									<tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">#</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ t('common.startTime') || 'Start' }}
										</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ t('common.endTime') || 'End' }}
										</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
											{{ t('common.status') || 'Status' }}
										</th>
										<th class="relative px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
											<span class="sr-only">Actions</span>
										</th>
									</tr>
								</thead>

								<tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
									<tr v-for="(slot, idx) in (week[day.value] || [])" :key="slot._key">
										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ idx + 1 }}
										</td>

										<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
											{{ slot.start_time }}
										</td>

										<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">
											{{ slot.end_time }}
										</td>

										<td class="px-5 py-4 text-sm whitespace-nowrap">
											<span
												class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
												:class="slot.is_active
													? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500'
													: 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'"
											>
												{{ slot.is_active ? (t('common.active') || 'Active') : (t('common.inactive') || 'Inactive') }}
											</span>
										</td>

										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
											<div class="flex items-center justify-center">
												<svg
													class="hover:fill-error-500 dark:hover:fill-error-500 cursor-pointer fill-gray-700 dark:fill-gray-400"
													width="20"
													height="20"
													viewBox="0 0 20 20"
													fill="none"
													xmlns="http://www.w3.org/2000/svg"
													@click="removeSlot(day.value, idx)"
												>
													<path
														fill-rule="evenodd"
														clip-rule="evenodd"
														d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z"
														fill=""
													/>
												</svg>
											</div>
										</td>
									</tr>

									<tr v-if="(week[day.value] || []).length === 0">
										<td colspan="5" class="px-5 py-4 text-center text-gray-400">
											{{ t('consultants.noWorkingHoursForDay') || 'No working hours added.' }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Add Slot Form (like products style) -->
					<div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
						<form @submit.prevent="addSlot(day.value)">
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-12">
								<!-- Start Time -->
								<div class="w-full lg:col-span-4">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('common.startTime') || 'Start Time' }}
									</label>

									<div class="relative">
										<flat-pickr
											v-model="slotForm[day.value].start_time"
											:config="flatpickrTimeConfig"
											class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
											:placeholder="t('common.selectTime') || 'Select time'"
										/>
										<span class="absolute text-gray-500 -translate-y-1/2 right-3 top-1/2 dark:text-gray-400">
											<svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path
													fill-rule="evenodd"
													clip-rule="evenodd"
													d="M3.04175 9.99984C3.04175 6.15686 6.1571 3.0415 10.0001 3.0415C13.8431 3.0415 16.9584 6.15686 16.9584 9.99984C16.9584 13.8428 13.8431 16.9582 10.0001 16.9582C6.1571 16.9582 3.04175 13.8428 3.04175 9.99984ZM10.0001 1.5415C5.32867 1.5415 1.54175 5.32843 1.54175 9.99984C1.54175 14.6712 5.32867 18.4582 10.0001 18.4582C14.6715 18.4582 18.4584 14.6712 18.4584 9.99984C18.4584 5.32843 14.6715 1.5415 10.0001 1.5415ZM9.99998 10.7498C9.58577 10.7498 9.24998 10.4141 9.24998 9.99984V5.4165C9.24998 5.00229 9.58577 4.6665 9.99998 4.6665C10.4142 4.6665 10.75 5.00229 10.75 5.4165V9.24984H13.3334C13.7476 9.24984 14.0834 9.58562 14.0834 9.99984C14.0834 10.4141 13.7476 10.7498 13.3334 10.7498H10.0001H9.99998Z"
													fill=""
												/>
											</svg>
										</span>
									</div>
								</div>

								<!-- End Time -->
								<div class="w-full lg:col-span-4">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('common.endTime') || 'End Time' }}
									</label>

									<div class="relative">
										<flat-pickr
											v-model="slotForm[day.value].end_time"
											:config="flatpickrTimeConfig"
											class="dark:bg-dark-900 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
											:placeholder="t('common.selectTime') || 'Select time'"
										/>
										<span class="absolute text-gray-500 -translate-y-1/2 right-3 top-1/2 dark:text-gray-400">
											<svg class="fill-current" width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path
													fill-rule="evenodd"
													clip-rule="evenodd"
													d="M3.04175 9.99984C3.04175 6.15686 6.1571 3.0415 10.0001 3.0415C13.8431 3.0415 16.9584 6.15686 16.9584 9.99984C16.9584 13.8428 13.8431 16.9582 10.0001 16.9582C6.1571 16.9582 3.04175 13.8428 3.04175 9.99984ZM10.0001 1.5415C5.32867 1.5415 1.54175 5.32843 1.54175 9.99984C1.54175 14.6712 5.32867 18.4582 10.0001 18.4582C14.6715 18.4582 18.4584 14.6712 18.4584 9.99984C18.4584 5.32843 14.6715 1.5415 10.0001 1.5415ZM9.99998 10.7498C9.58577 10.7498 9.24998 10.4141 9.24998 9.99984V5.4165C9.24998 5.00229 9.58577 4.6665 9.99998 4.6665C10.4142 4.6665 10.75 5.00229 10.75 5.4165V9.24984H13.3334C13.7476 9.24984 14.0834 9.58562 14.0834 9.99984C14.0834 10.4141 13.7476 10.7498 13.3334 10.7498H10.0001H9.99998Z"
													fill=""
												/>
											</svg>
										</span>
									</div>
								</div>

								<!-- Active -->
								<div class="w-full lg:col-span-2">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('common.status') || 'Status' }}
									</label>

									<div class="flex h-11 items-center gap-3 rounded-lg border border-gray-300 bg-white px-3 dark:border-gray-700 dark:bg-gray-900">
										<input
											:id="`wh-active-${day.value}`"
											type="checkbox"
											v-model="slotForm[day.value].is_active"
											class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/10 dark:border-gray-700"
										/>
										<label :for="`wh-active-${day.value}`" class="text-sm text-gray-700 dark:text-gray-400">
											{{ slotForm[day.value].is_active ? (t('common.active') || 'Active') : (t('common.inactive') || 'Inactive') }}
										</label>
									</div>
								</div>

								<!-- Add button -->
								<div class="flex w-full items-end lg:col-span-2">
									<button
										type="submit"
										class="hover:bg-brand-600 bg-brand-500 h-11 w-full rounded-lg px-4 py-3 text-sm font-medium text-white transition"
									>
										{{ t('buttons.add') || 'Add' }}
									</button>
								</div>
							</div>

							<p v-if="slotFormErrors[day.value]" class="mt-2 text-sm text-error-500">
								{{ slotFormErrors[day.value] }}
							</p>
						</form>
					</div>
				</div>

				<!-- Save weekly schedule -->
				<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
					<button
						type="button"
						@click="saveWorkingHours"
						class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
						:class="{ 'cursor-not-allowed opacity-70': workingHoursForm.processing }"
						:disabled="workingHoursForm.processing"
					>
						{{ workingHoursForm.processing ? (t('common.loading') || 'Loading...') : (t('buttons.saveWorkingHours') || 'Save Working Hours') }}
					</button>
				</div>

				<!-- Server validation errors for week -->
				<div v-if="Object.keys(workingHoursForm.errors || {}).length" class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
					<div class="font-semibold mb-1">{{ t('common.validationErrors') || 'Validation errors' }}</div>
					<ul class="list-disc pl-5 space-y-1">
						<li v-for="(msg, key) in workingHoursForm.errors" :key="key">
							<span class="font-medium">{{ key }}:</span> {{ msg }}
						</li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Profile Image -->
		<ImageUploadBox
			v-model="form.profile_image"
			input-id="consultant-profile-image"
			label="consultants.profileImage"
			:initial-image="consultant.profile_image ? `/storage/${consultant.profile_image}` : null"
		/>
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
import { computed, reactive, ref } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { useNotifications } from '@/composables/useNotifications'
import ImageUploadBox from '@/Components/common/ImageUploadBox.vue'
import { route } from '@/route'

// flatpickr (time)
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'

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

/**
 * =========================
 * Working Hours (Weekly UI)
 * =========================
 */

// Flatpickr config (time only)
const flatpickrTimeConfig = {
	enableTime: true,
	noCalendar: true,
	dateFormat: 'H:i',
	time_24hr: true,
}

// Days
const daysOfWeek = computed(() => {
	// 0=Sunday ... 6=Saturday
	const ar = [
		{ value: 0, label: 'الأحد' },
		{ value: 1, label: 'الإثنين' },
		{ value: 2, label: 'الثلاثاء' },
		{ value: 3, label: 'الأربعاء' },
		{ value: 4, label: 'الخميس' },
		{ value: 5, label: 'الجمعة' },
		{ value: 6, label: 'السبت' },
	]
	const en = [
		{ value: 0, label: 'Sunday' },
		{ value: 1, label: 'Monday' },
		{ value: 2, label: 'Tuesday' },
		{ value: 3, label: 'Wednesday' },
		{ value: 4, label: 'Thursday' },
		{ value: 5, label: 'Friday' },
		{ value: 6, label: 'Saturday' },
	]
	return (String(locale.value) === 'ar') ? ar : en
})

// Build week map from consultant.working_hours (DTO should provide it)
function buildWeekFromProps() {
	const base = { 0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: [] }

	const list = consultant?.working_hours || consultant?.workingHours || []
	for (const wh of (Array.isArray(list) ? list : [])) {
		const day = Number(wh.day_of_week)
		if (!(day in base)) continue

		base[day].push({
			id: wh.id ?? null,
			start_time: wh.start_time,
			end_time: wh.end_time,
			is_active: wh.is_active ?? true,
			_key: `${wh.id ?? 'new'}-${Math.random().toString(16).slice(2)}`,
		})
	}

	// sort by start_time
	for (const d of Object.keys(base)) {
		base[d].sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)))
	}

	return base
}

const week = reactive(buildWeekFromProps())

// per-day add form
const slotForm = reactive({
	0: { start_time: '', end_time: '', is_active: true },
	1: { start_time: '', end_time: '', is_active: true },
	2: { start_time: '', end_time: '', is_active: true },
	3: { start_time: '', end_time: '', is_active: true },
	4: { start_time: '', end_time: '', is_active: true },
	5: { start_time: '', end_time: '', is_active: true },
	6: { start_time: '', end_time: '', is_active: true },
})

const slotFormErrors = reactive({
	0: '',
	1: '',
	2: '',
	3: '',
	4: '',
	5: '',
	6: '',
})

const workingHoursError = ref('')

// Inertia form for weekly save
const workingHoursForm = useForm({
	week: {},
})

function removeSlot(day, idx) {
	week[day].splice(idx, 1)
}

function addSlot(day) {
	slotFormErrors[day] = ''
	workingHoursError.value = ''

	const start = slotForm[day].start_time
	const end = slotForm[day].end_time

	if (!start || !end) {
		slotFormErrors[day] = (t('common.startEndRequired') || 'Start and end time are required.')
		return
	}

	if (String(end) <= String(start)) {
		slotFormErrors[day] = (t('common.endAfterStart') || 'End time must be after start time.')
		return
	}

	// client-side overlap check within same day
	const existsOverlap = (week[day] || []).some(s => String(start) < String(s.end_time) && String(end) > String(s.start_time))
	if (existsOverlap) {
		slotFormErrors[day] = (t('consultants.workingHoursOverlap') || 'This time range overlaps with another range.')
		return
	}

	// prevent exact duplicate
	const isDuplicate = (week[day] || []).some(s => String(s.start_time) === String(start) && String(s.end_time) === String(end))
	if (isDuplicate) {
		slotFormErrors[day] = (t('consultants.workingHoursDuplicate') || 'This time range already exists.')
		return
	}

	week[day].push({
		id: null,
		start_time: start,
		end_time: end,
		is_active: !!slotForm[day].is_active,
		_key: `new-${Date.now()}-${Math.random().toString(16).slice(2)}`,
	})

	// sort
	week[day].sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)))

	// reset
	slotForm[day].start_time = ''
	slotForm[day].end_time = ''
	slotForm[day].is_active = true
}

function toPayloadWeek() {
	const payload = {}
	for (const d of [0, 1, 2, 3, 4, 5, 6]) {
		payload[d] = (week[d] || []).map(s => ({
			start_time: s.start_time,
			end_time: s.end_time,
			is_active: !!s.is_active,
		}))
	}
	return payload
}

function saveWorkingHours() {
	workingHoursError.value = ''
	workingHoursForm.clearErrors()

	workingHoursForm.week = toPayloadWeek()

	workingHoursForm.put(route('admin.consultants.working-hours.replace', consultant.id), {
		onSuccess: () => success(t('consultants.workingHoursSavedSuccessfully') || 'Working hours saved successfully'),
		onError: () => error(t('consultants.workingHoursSaveFailed') || 'Failed to save working hours'),
		preserveScroll: true,
	})
}
</script>