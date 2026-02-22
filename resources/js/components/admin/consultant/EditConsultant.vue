<template>
	<form class="space-y-6" @submit.prevent="update">
		<!-- Tabs Header -->
		<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
			<div class="border-b border-gray-200 px-4 py-3 dark:border-gray-800 sm:px-6">
				<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
					<h2 class="text-lg font-medium text-gray-800 dark:text-white">
						{{ t('consultants.editConsultant') || 'تعديل المستشار' }}
					</h2>

					<div
						class="inline-flex w-full rounded-xl bg-gray-100 p-1 dark:bg-gray-900 sm:w-auto"
						role="tablist"
						aria-label="Consultant tabs"
					>
						<button
							type="button"
							role="tab"
							:aria-selected="activeTab === 'info'"
							@click="activeTab = 'info'"
							class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
							:class="activeTab === 'info'
								? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
								: 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
						>
							{{ t('consultants.consultantInformation') || 'معلومات المستشار' }}
						</button>

						<button
							type="button"
							role="tab"
							:aria-selected="activeTab === 'hours'"
							@click="activeTab = 'hours'"
							class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
							:class="activeTab === 'hours'
								? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
								: 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
						>
							{{ t('consultants.workingHours') || 'ساعات العمل' }}
						</button>

						<button
							type="button"
							role="tab"
							:aria-selected="activeTab === 'experiences'"
							@click="activeTab = 'experiences'"
							class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
							:class="activeTab === 'experiences'
								? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
								: 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
						>
							{{ t('consultants.experiences') || 'الخبرات' }}
						</button>

						<button
							type="button"
							role="tab"
							:aria-selected="activeTab === 'holidays'"
							@click="activeTab = 'holidays'"
							class="flex-1 rounded-lg px-4 py-2 text-sm font-medium transition sm:flex-none"
							:class="activeTab === 'holidays'
								? 'bg-white text-gray-900 shadow-theme-xs dark:bg-white/[0.06] dark:text-white'
								: 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
						>
							{{ t('consultants.holidays') || 'الإجازات' }}
						</button>
					</div>
				</div>
			</div>

			<div class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 sm:px-6">
				<span v-if="activeTab === 'info'">
					{{ t('consultants.updateInfoHint') || 'تحديث بيانات المستشار الأساسية.' }}
				</span>
				<span v-else-if="activeTab === 'hours'">
					{{ t('consultants.updateHoursHint') || 'إدارة ساعات العمل الأسبوعية.' }}
				</span>
				<span v-else-if="activeTab === 'experiences'">
					{{ t('consultants.updateExperiencesHint') || 'إدارة خبرات المستشار.' }}
				</span>
				<span v-else>
					{{ t('consultants.updateHolidaysHint') || 'إدارة إجازات المستشار.' }}
				</span>
			</div>
		</div>

		<!-- ========================= -->
		<!-- Tab: Consultant Info -->
		<!-- ========================= -->
		<div v-show="activeTab === 'info'" role="tabpanel" class="space-y-6">
			<!-- Consultant Information -->
			<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
					<h2 class="text-lg font-medium text-gray-800 dark:text-white">
						{{ t('consultants.consultantInformation') || 'معلومات المستشار' }}
					</h2>
				</div>

				<div class="p-4 sm:p-6">
					<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
						<!-- First Name -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('profile.labels.firstName') || 'الاسم الأول' }}
							</label>
							<input
								v-model="form.first_name"
								type="text"
								autocomplete="given-name"
								:placeholder="t('profile.labels.firstName') || 'الاسم الأول'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.first_name" class="mt-1 text-sm text-error-500">{{ form.errors.first_name }}</p>
						</div>

						<!-- Last Name -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('profile.labels.lastName') || 'الاسم الأخير' }}
							</label>
							<input
								v-model="form.last_name"
								type="text"
								autocomplete="family-name"
								:placeholder="t('profile.labels.lastName') || 'الاسم الأخير'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.last_name" class="mt-1 text-sm text-error-500">{{ form.errors.last_name }}</p>
						</div>

						<!-- Email -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('common.email') || 'البريد الإلكتروني' }}
							</label>
							<input
								v-model="form.email"
								type="email"
								autocomplete="email"
								:placeholder="t('users.emailPlaceholder') || 'example@domain.com'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.email" class="mt-1 text-sm text-error-500">{{ form.errors.email }}</p>
						</div>

						<!-- Phone Number -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('common.phoneNumber') || 'رقم الهاتف' }}
							</label>
							<input
								v-model="form.phone_number"
								type="tel"
								autocomplete="tel"
								:placeholder="t('users.phonePlaceholder') || '05xxxxxxxx'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.phone_number" class="mt-1 text-sm text-error-500">{{ form.errors.phone_number }}</p>
						</div>

						<!-- Gender -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('certificate.gender') || 'الجنس' }}
							</label>
							<div class="relative z-20 bg-transparent">
								<select
									v-model="form.gender"
									class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
								>
									<option value="" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">--</option>
									<option value="male" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ t('certificate.genders.male') || 'ذكر' }}</option>
									<option value="female" class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">{{ t('certificate.genders.female') || 'أنثى' }}</option>
								</select>
								<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
									<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
										<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</div>
							<p v-if="form.errors.gender" class="mt-1 text-sm text-error-500">{{ form.errors.gender }}</p>
						</div>

						<!-- Consultation Type -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('consultationTypes.consultationType') || 'نوع الاستشارة' }}
							</label>
							<div class="relative z-20 bg-transparent">
								<select
									v-model="form.consultation_type_id"
									class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none px-4 py-2.5 pr-11 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
								>
									<option value="" disabled class="text-gray-700 dark:bg-gray-900 dark:text-gray-400">--</option>
									<option
										v-for="ct in consultation_types"
										:key="ct.id"
										:value="ct.id"
										class="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
									>
										{{ ct.name }}
									</option>
								</select>
								<span class="pointer-events-none absolute top-1/2 right-4 z-30 -translate-y-1/2 text-gray-700 dark:text-gray-400">
									<svg class="stroke-current" width="20" height="20" viewBox="0 0 20 20" fill="none">
										<path d="M4.79175 7.396L10.0001 12.6043L15.2084 7.396" stroke="" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
							</div>
							<p v-if="form.errors.consultation_type_id" class="mt-1 text-sm text-error-500">{{ form.errors.consultation_type_id }}</p>
						</div>

						<!-- Years of Experience -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('consultationTypes.years_of_experience') || 'سنوات الخبرة' }}
							</label>
							<input
								v-model.number="form.years_of_experience"
								type="number"
								min="0"
								max="80"
								:placeholder="t('consultationTypes.years_of_experience') || 'سنوات الخبرة'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.years_of_experience" class="mt-1 text-sm text-error-500">{{ form.errors.years_of_experience }}</p>
						</div>

						<!-- Price per Hour -->
						<div>
							<label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
								{{ t('consultants.pricePerHour') || 'السعر بالساعة' }}
							</label>
							<input
								v-model.number="form.price"
								type="number"
								min="0"
								step="0.01"
								:placeholder="t('consultants.pricePerHour') || 'السعر بالساعة'"
								class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
							/>
							<p v-if="form.errors.price" class="mt-1 text-sm text-error-500">{{ form.errors.price }}</p>
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
					</div>
				</div>
			</div>

			<!-- Profile Image -->
			<ImageUploadBox
				v-model="form.avatar"
				input-id="consultant-profile-image"
				label="consultants.profileImage"
				:initial-image="consultant.avatar ? `/storage/${consultant.avatar}` : null"
			/>
			<p v-if="form.errors.avatar" class="mt-1 text-sm text-error-500">{{ form.errors.avatar }}</p>

			<!-- Buttons (Update) -->
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
		</div>

		<!-- ========================= -->
		<!-- Tab: Working Hours -->
		<!-- ========================= -->
		<div v-show="activeTab === 'hours'" role="tabpanel" class="space-y-6">
			<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
					<h2 class="text-lg font-medium text-gray-800 dark:text-white">
						{{ t('consultants.workingHours') || 'ساعات العمل' }}
					</h2>
				</div>

				<div class="p-4 sm:p-6 space-y-6">
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
							<span class="text-xs text-gray-500 dark:text-gray-400">
								{{ (week[day.value]?.length || 0) }} {{ t('common.items') || 'عناصر' }}
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
												{{ t('common.startTime') || 'البداية' }}
											</th>
											<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
												{{ t('common.endTime') || 'النهاية' }}
											</th>
											<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
												{{ t('common.status') || 'الحالة' }}
											</th>
											<th class="relative px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
												<span class="sr-only">Actions</span>
											</th>
										</tr>
									</thead>

									<tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
										<tr v-for="(slot, idx) in (week[day.value] || [])" :key="slot._key">
											<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
											<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ slot.start_time }}</td>
											<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ slot.end_time }}</td>
											<td class="px-5 py-4 text-sm whitespace-nowrap">
												<span
													class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
													:class="slot.is_active
														? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500'
														: 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'"
												>
													{{ slot.is_active ? (t('common.active') || 'نشط') : (t('common.inactive') || 'غير نشط') }}
												</span>
											</td>
											<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
												<div class="flex items-center justify-center">
													<svg
														class="hover:fill-error-500 dark:hover:fill-error-500 cursor-pointer fill-gray-700 dark:fill-gray-400"
														width="20" height="20" viewBox="0 0 20 20" fill="none"
														@click="removeSlot(day.value, idx)"
													>
														<path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill="" />
													</svg>
												</div>
											</td>
										</tr>
										<tr v-if="(week[day.value] || []).length === 0">
											<td colspan="5" class="px-5 py-4 text-center text-gray-400">
												{{ t('consultants.noWorkingHoursForDay') || 'لا توجد ساعات عمل مضافة.' }}
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>

						<!-- Add Slot Form -->
						<div class="mt-5 rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
							<form @submit.prevent="addSlot(day.value)">
								<div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
									<div class="w-full">
										<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
											{{ t('common.startTime') || 'وقت البداية' }}
										</label>
										<input
											v-model="slotForm[day.value].start_time"
											type="time"
											class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
										/>
									</div>
									<div class="w-full">
										<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
											{{ t('common.endTime') || 'وقت النهاية' }}
										</label>
										<input
											v-model="slotForm[day.value].end_time"
											type="time"
											class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
										/>
									</div>
									<div class="w-full flex items-center pt-6">
										<label class="flex cursor-pointer select-none items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
											<input type="checkbox" v-model="slotForm[day.value].is_active" class="h-4 w-4 rounded border-gray-300 text-brand-500" />
											{{ slotForm[day.value].is_active ? (t('common.active') || 'نشط') : (t('common.inactive') || 'غير نشط') }}
										</label>
									</div>
									<div class="flex w-full items-end">
										<button type="submit" class="hover:bg-brand-600 bg-brand-500 h-11 w-full rounded-lg px-4 py-3 text-sm font-medium text-white transition">
											{{ t('buttons.add') || 'إضافة' }}
										</button>
									</div>
								</div>
								<p v-if="slotFormErrors[day.value]" class="mt-2 text-sm text-error-500">{{ slotFormErrors[day.value] }}</p>
							</form>
						</div>
					</div>

					<!-- Save working hours -->
					<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
						<button
							type="button"
							@click="saveWorkingHours"
							class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
							:class="{ 'cursor-not-allowed opacity-70': workingHoursForm.processing }"
							:disabled="workingHoursForm.processing"
						>
							{{ workingHoursForm.processing ? (t('common.loading') || 'جاري الحفظ...') : (t('buttons.saveWorkingHours') || 'حفظ ساعات العمل') }}
						</button>
					</div>

					<div v-if="Object.keys(workingHoursForm.errors || {}).length" class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
						<div class="font-semibold mb-1">{{ t('common.validationErrors') || 'أخطاء التحقق' }}</div>
						<ul class="list-disc pl-5 space-y-1">
							<li v-for="(msg, key) in workingHoursForm.errors" :key="key">
								<span class="font-medium">{{ key }}:</span> {{ msg }}
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
				<Link
					:href="route('admin.consultants.index')"
					class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
				>
					{{ t('buttons.backToList') }}
				</Link>
			</div>
		</div>

		<!-- ========================= -->
		<!-- Tab: Experiences -->
		<!-- ========================= -->
		<div v-show="activeTab === 'experiences'" role="tabpanel" class="space-y-6">
			<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
					<h2 class="text-lg font-medium text-gray-800 dark:text-white">
						{{ t('consultants.experiences') || 'الخبرات' }}
					</h2>
				</div>

				<div class="p-4 sm:p-6 space-y-6">
					<p v-if="experienceError" class="text-sm text-error-500">{{ experienceError }}</p>

					<!-- Existing experiences table -->
					<div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
						<div class="custom-scrollbar overflow-x-auto">
							<table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
								<thead class="bg-gray-50 dark:bg-gray-900">
									<tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">#</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ t('consultants.experienceName') || 'الخبرة' }}
										</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
											{{ t('common.status') || 'الحالة' }}
										</th>
										<th class="relative px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
											<span class="sr-only">Actions</span>
										</th>
									</tr>
								</thead>

								<tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
									<tr v-for="(exp, idx) in experiences" :key="exp._key">
										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
										<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ exp.name }}</td>
										<td class="px-5 py-4 text-sm whitespace-nowrap">
											<span
												class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
												:class="exp.is_active
													? 'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500'
													: 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500'"
											>
												{{ exp.is_active ? (t('common.active') || 'نشط') : (t('common.inactive') || 'غير نشط') }}
											</span>
										</td>
										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
											<div class="flex items-center justify-center">
												<svg
													class="hover:fill-error-500 dark:hover:fill-error-500 cursor-pointer fill-gray-700 dark:fill-gray-400"
													width="20" height="20" viewBox="0 0 20 20" fill="none"
													@click="removeExperience(idx)"
												>
													<path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill="" />
												</svg>
											</div>
										</td>
									</tr>
									<tr v-if="experiences.length === 0">
										<td colspan="4" class="px-5 py-4 text-center text-gray-400">
											{{ t('consultants.noExperiences') || 'لا توجد خبرات مضافة.' }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Add Experience Form -->
					<div class="rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
						<form @submit.prevent="addExperience">
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
								<div class="w-full sm:col-span-2">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('consultants.experienceName') || 'اسم الخبرة' }}
									</label>
									<input
										v-model="experienceForm.name"
										type="text"
										autocomplete="off"
										:placeholder="t('consultants.experienceNamePlaceholder') || 'مثال: مستشار أول في ...'"
										class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
									/>
								</div>
								<div class="w-full flex items-center">
									<label class="flex cursor-pointer select-none items-center gap-3 text-sm font-medium text-gray-700 dark:text-gray-400">
										<div class="relative">
											<input type="checkbox" class="sr-only" v-model="experienceForm.is_active" />
											<div class="block h-6 w-11 rounded-full" :class="experienceForm.is_active ? 'bg-brand-500' : 'bg-gray-200 dark:bg-white/10'"></div>
											<div :class="experienceForm.is_active ? 'translate-x-full' : 'translate-x-0'" class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-theme-sm duration-300 ease-linear"></div>
										</div>
										<span
											class="inline-flex items-center justify-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium"
											:class="{
												'bg-green-50 text-green-600 dark:bg-green-500/15 dark:text-green-500': experienceForm.is_active,
												'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500': !experienceForm.is_active,
											}"
										>
											{{ experienceForm.is_active ? t('common.active') : t('common.inactive') }}
										</span>
									</label>
								</div>
							</div>
							<p v-if="experienceFormError" class="mt-2 text-sm text-error-500">{{ experienceFormError }}</p>
							<div class="mt-4 flex w-full items-end sm:justify-end">
								<button type="submit" class="hover:bg-brand-600 bg-brand-500 h-11 w-full rounded-lg px-4 py-3 text-sm font-medium text-white transition sm:w-auto">
									{{ t('consultants.addExperience') || 'إضافة خبرة' }}
								</button>
							</div>
						</form>
					</div>

					<!-- Save experiences -->
					<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
						<button
							type="button"
							@click="saveExperiences"
							class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
							:class="{ 'cursor-not-allowed opacity-70': experiencesForm.processing }"
							:disabled="experiencesForm.processing"
						>
							{{ experiencesForm.processing ? (t('common.loading') || 'جاري الحفظ...') : (t('consultants.saveExperiences') || 'حفظ الخبرات') }}
						</button>
					</div>

					<div v-if="Object.keys(experiencesForm.errors || {}).length" class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
						<div class="font-semibold mb-1">{{ t('common.validationErrors') || 'أخطاء التحقق' }}</div>
						<ul class="list-disc pl-5 space-y-1">
							<li v-for="(msg, key) in experiencesForm.errors" :key="key">
								<span class="font-medium">{{ key }}:</span> {{ msg }}
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
				<Link
					:href="route('admin.consultants.index')"
					class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
				>
					{{ t('buttons.backToList') }}
				</Link>
			</div>
		</div>

		<!-- ========================= -->
		<!-- Tab: Holidays -->
		<!-- ========================= -->
		<div v-show="activeTab === 'holidays'" role="tabpanel" class="space-y-6">
			<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
				<div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
					<h2 class="text-lg font-medium text-gray-800 dark:text-white">
						{{ t('consultants.holidays') || 'الإجازات' }}
					</h2>
				</div>

				<div class="p-4 sm:p-6 space-y-6">
					<p v-if="holidayError" class="text-sm text-error-500">{{ holidayError }}</p>

					<!-- Existing holidays table -->
					<div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
						<div class="custom-scrollbar overflow-x-auto">
							<table class="min-w-full text-left text-sm text-gray-700 dark:border-gray-800">
								<thead class="bg-gray-50 dark:bg-gray-900">
									<tr class="border-b border-gray-100 whitespace-nowrap dark:border-gray-800">
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">#</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ t('consultants.holidayDate') || 'تاريخ الإجازة' }}
										</th>
										<th class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
											{{ t('consultants.holidayName') || 'اسم الإجازة' }}
										</th>
										<th class="relative px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-700 dark:text-gray-400">
											<span class="sr-only">Actions</span>
										</th>
									</tr>
								</thead>

								<tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-white/[0.03]">
									<tr v-for="(holiday, idx) in holidays" :key="holiday._key">
										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">{{ idx + 1 }}</td>
										<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ holiday.holiday_date }}</td>
										<td class="px-5 py-4 text-sm font-medium whitespace-nowrap text-gray-800 dark:text-white/90">{{ holiday.name || '—' }}</td>
										<td class="px-5 py-4 text-sm whitespace-nowrap text-gray-500 dark:text-gray-400">
											<div class="flex items-center justify-center">
												<svg
													class="hover:fill-error-500 dark:hover:fill-error-500 cursor-pointer fill-gray-700 dark:fill-gray-400"
													width="20" height="20" viewBox="0 0 20 20" fill="none"
													@click="removeHoliday(idx)"
												>
													<path fill-rule="evenodd" clip-rule="evenodd" d="M6.54142 3.7915C6.54142 2.54886 7.54878 1.5415 8.79142 1.5415H11.2081C12.4507 1.5415 13.4581 2.54886 13.4581 3.7915V4.0415H15.6252H16.666C17.0802 4.0415 17.416 4.37729 17.416 4.7915C17.416 5.20572 17.0802 5.5415 16.666 5.5415H16.3752V8.24638V13.2464V16.2082C16.3752 17.4508 15.3678 18.4582 14.1252 18.4582H5.87516C4.63252 18.4582 3.62516 17.4508 3.62516 16.2082V13.2464V8.24638V5.5415H3.3335C2.91928 5.5415 2.5835 5.20572 2.5835 4.7915C2.5835 4.37729 2.91928 4.0415 3.3335 4.0415H4.37516H6.54142V3.7915ZM14.8752 13.2464V8.24638V5.5415H13.4581H12.7081H7.29142H6.54142H5.12516V8.24638V13.2464V16.2082C5.12516 16.6224 5.46095 16.9582 5.87516 16.9582H14.1252C14.5394 16.9582 14.8752 16.6224 14.8752 16.2082V13.2464ZM8.04142 4.0415H11.9581V3.7915C11.9581 3.37729 11.6223 3.0415 11.2081 3.0415H8.79142C8.37721 3.0415 8.04142 3.37729 8.04142 3.7915V4.0415ZM8.3335 7.99984C8.74771 7.99984 9.0835 8.33562 9.0835 8.74984V13.7498C9.0835 14.1641 8.74771 14.4998 8.3335 14.4998C7.91928 14.4998 7.5835 14.1641 7.5835 13.7498V8.74984C7.5835 8.33562 7.91928 7.99984 8.3335 7.99984ZM12.4168 8.74984C12.4168 8.33562 12.081 7.99984 11.6668 7.99984C11.2526 7.99984 10.9168 8.33562 10.9168 8.74984V13.7498C10.9168 14.1641 11.2526 14.4998 11.6668 14.4998C12.081 14.4998 12.4168 14.1641 12.4168 13.7498V8.74984Z" fill="" />
												</svg>
											</div>
										</td>
									</tr>
									<tr v-if="holidays.length === 0">
										<td colspan="4" class="px-5 py-4 text-center text-gray-400">
											{{ t('consultants.noHolidays') || 'لا توجد إجازات مضافة.' }}
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<!-- Add Holiday Form -->
					<div class="rounded-xl border border-gray-100 bg-gray-50 p-4 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
						<form @submit.prevent="addHoliday">
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
								<div class="w-full">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('consultants.holidayDate') || 'تاريخ الإجازة' }}
									</label>
									<input
										v-model="holidayForm.holiday_date"
										type="date"
										class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
									/>
								</div>
								<div class="w-full">
									<label class="mb-1 inline-block text-sm font-semibold text-gray-700 dark:text-gray-400">
										{{ t('consultants.holidayName') || 'اسم الإجازة' }}
									</label>
									<input
										v-model="holidayForm.name"
										type="text"
										autocomplete="off"
										class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
										:placeholder="t('consultants.holidayName') || 'اسم الإجازة'"
									/>
								</div>
								<div class="flex w-full items-end">
									<button type="submit" class="hover:bg-brand-600 bg-brand-500 h-11 w-full rounded-lg px-4 py-3 text-sm font-medium text-white transition">
										{{ t('buttons.add') || 'إضافة' }}
									</button>
								</div>
							</div>
							<p v-if="holidayFormError" class="mt-2 text-sm text-error-500">{{ holidayFormError }}</p>
						</form>
					</div>

					<!-- Save holidays -->
					<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
						<button
							type="button"
							@click="saveHolidays"
							class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-sm font-medium text-white transition"
							:class="{ 'cursor-not-allowed opacity-70': holidaysForm.processing }"
							:disabled="holidaysForm.processing"
						>
							{{ holidaysForm.processing ? (t('common.loading') || 'جاري الحفظ...') : (t('consultants.saveHolidays') || 'حفظ الإجازات') }}
						</button>
					</div>

					<div v-if="Object.keys(holidaysForm.errors || {}).length" class="rounded-lg border border-error-200 bg-error-50 p-4 text-sm text-error-700 dark:border-error-500/30 dark:bg-error-500/10 dark:text-error-300">
						<div class="font-semibold mb-1">{{ t('common.validationErrors') || 'أخطاء التحقق' }}</div>
						<ul class="list-disc pl-5 space-y-1">
							<li v-for="(msg, key) in holidaysForm.errors" :key="key">
								<span class="font-medium">{{ key }}:</span> {{ msg }}
							</li>
						</ul>
					</div>
				</div>
			</div>

			<div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
				<Link
					:href="route('admin.consultants.index')"
					class="shadow-theme-xs inline-flex items-center justify-center gap-2 rounded-lg bg-white px-4 py-3 text-sm font-medium text-gray-700 ring-1 ring-gray-300 transition hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700 dark:hover:bg-white/[0.03]"
				>
					{{ t('buttons.backToList') }}
				</Link>
			</div>
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

const { t, locale } = useI18n()
const { success, error } = useNotifications()

const activeTab = ref('info')

const props = defineProps({
	consultant: { type: Object, required: true },
	consultation_types: { type: Array, required: true },
})

const consultant = props.consultant || {}
const consultation_types = computed(() => props.consultation_types || [])

// Form for consultant info (Tab 1)
const form = useForm({
	first_name: consultant.first_name ?? '',
	last_name: consultant.last_name ?? '',
	email: consultant.user_email ?? '',
	phone_number: consultant.user_phone ?? '',
	gender: consultant.gender ?? '',
	consultation_type_id: consultant.consultation_type_id ?? '',
	years_of_experience: consultant.years_of_experience ?? '',
	avatar: null,
	price: consultant.price ?? '',
	is_active: consultant.is_active ?? true,
})

function update() {
	form.transform((data) => ({
		...data,
		_method: 'PATCH',
	})).post(route('admin.consultants.update', consultant.id), {
		onSuccess: () => success(t('consultants.consultantUpdatedSuccessfully') || 'تم تحديث المستشار بنجاح'),
		onError: () => error(t('consultants.consultantUpdateFailed') || 'فشل تحديث المستشار'),
		preserveScroll: true,
		forceFormData: true,
	})
}

/**
 * =========================
 * Working Hours (Weekly UI)
 * =========================
 */

const daysOfWeek = computed(() => {
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

function buildWeekFromProps() {
	const base = { 0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: [] }
	const list = consultant?.working_hours || []
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
	for (const d of Object.keys(base)) {
		base[d].sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)))
	}
	return base
}

const week = reactive(buildWeekFromProps())

const slotForm = reactive({
	0: { start_time: '', end_time: '', is_active: true },
	1: { start_time: '', end_time: '', is_active: true },
	2: { start_time: '', end_time: '', is_active: true },
	3: { start_time: '', end_time: '', is_active: true },
	4: { start_time: '', end_time: '', is_active: true },
	5: { start_time: '', end_time: '', is_active: true },
	6: { start_time: '', end_time: '', is_active: true },
})

const slotFormErrors = reactive({ 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' })
const workingHoursError = ref('')
const workingHoursForm = useForm({ week: {} })

function removeSlot(day, idx) {
	week[day].splice(idx, 1)
}

function addSlot(day) {
	slotFormErrors[day] = ''
	workingHoursError.value = ''
	const start = slotForm[day].start_time
	const end = slotForm[day].end_time
	if (!start || !end) {
		slotFormErrors[day] = t('common.startEndRequired') || 'وقت البداية والنهاية مطلوبان.'
		return
	}
	if (String(end) <= String(start)) {
		slotFormErrors[day] = t('common.endAfterStart') || 'وقت النهاية يجب أن يكون بعد وقت البداية.'
		return
	}
	const existsOverlap = (week[day] || []).some(s => String(start) < String(s.end_time) && String(end) > String(s.start_time))
	if (existsOverlap) {
		slotFormErrors[day] = t('consultants.workingHoursOverlap') || 'هذا النطاق الزمني يتداخل مع نطاق آخر.'
		return
	}
	const isDuplicate = (week[day] || []).some(s => String(s.start_time) === String(start) && String(s.end_time) === String(end))
	if (isDuplicate) {
		slotFormErrors[day] = t('consultants.workingHoursDuplicate') || 'هذا النطاق الزمني موجود بالفعل.'
		return
	}
	week[day].push({
		id: null,
		start_time: start,
		end_time: end,
		is_active: !!slotForm[day].is_active,
		_key: `new-${Date.now()}-${Math.random().toString(16).slice(2)}`,
	})
	week[day].sort((a, b) => String(a.start_time).localeCompare(String(b.start_time)))
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
		onSuccess: () => success(t('consultants.workingHoursSavedSuccessfully') || 'تم حفظ ساعات العمل بنجاح'),
		onError: () => error(t('consultants.workingHoursSaveFailed') || 'فشل حفظ ساعات العمل'),
		preserveScroll: true,
	})
}

/**
 * =========================
 * Experiences UI / Save
 * =========================
 */

function buildExperiencesFromProps() {
	const list = Array.isArray(consultant?.experiences) ? consultant.experiences : []
	return list.map(exp => ({
		id: exp.id ?? null,
		name: exp.name || '',
		is_active: exp.is_active ?? true,
		_key: `${exp.id ?? 'new'}-${Math.random().toString(16).slice(2)}`,
	})).sort((a, b) => String(a.name).localeCompare(String(b.name)))
}

const experiences = ref(buildExperiencesFromProps())
const experienceForm = reactive({ name: '', is_active: true })
const experienceFormError = ref('')
const experienceError = ref('')
const experiencesForm = useForm({ experiences: [] })

function resetExperienceForm() {
	experienceForm.name = ''
	experienceForm.is_active = true
}

function addExperience() {
	experienceFormError.value = ''
	experienceError.value = ''
	const name = experienceForm.name ? experienceForm.name.trim() : ''
	if (!name) {
		experienceFormError.value = t('consultants.experienceNameRequired') || 'اسم الخبرة مطلوب.'
		return
	}
	const exists = experiences.value.some(exp => String(exp.name).trim().toLowerCase() === name.toLowerCase())
	if (exists) {
		experienceFormError.value = t('consultants.experienceDuplicate') || 'الخبرة مضافة بالفعل.'
		return
	}
	experiences.value.push({
		id: null,
		name,
		is_active: !!experienceForm.is_active,
		_key: `new-${Date.now()}-${Math.random().toString(16).slice(2)}`,
	})
	experiences.value.sort((a, b) => String(a.name).localeCompare(String(b.name)))
	resetExperienceForm()
}

function removeExperience(idx) {
	experienceFormError.value = ''
	experienceError.value = ''
	experiences.value.splice(idx, 1)
}

function toExperiencePayload() {
	return experiences.value.map(exp => ({
		name: exp.name,
		is_active: !!exp.is_active,
	}))
}

function saveExperiences() {
	experienceError.value = ''
	experienceFormError.value = ''
	experiencesForm.clearErrors()
	experiencesForm.experiences = toExperiencePayload()
	experiencesForm.put(route('admin.consultants.experiences.replace', consultant.id), {
		onSuccess: () => success(t('consultants.experiencesSavedSuccessfully') || 'تم حفظ الخبرات بنجاح'),
		onError: () => error(t('consultants.experiencesSaveFailed') || 'فشل حفظ الخبرات'),
		preserveScroll: true,
	})
}

/**
 * =========================
 * Holidays UI / Save
 * =========================
 */

function buildHolidaysFromProps() {
	const list = Array.isArray(consultant?.holidays) ? consultant.holidays : []
	return list.map(h => ({
		id: h.id ?? null,
		holiday_date: h.holiday_date,
		name: h.name || '',
		_key: `${h.id ?? 'new'}-${Math.random().toString(16).slice(2)}`,
	})).sort((a, b) => String(a.holiday_date).localeCompare(String(b.holiday_date)))
}

const holidays = ref(buildHolidaysFromProps())
const holidayForm = reactive({ holiday_date: '', name: '' })
const holidayFormError = ref('')
const holidayError = ref('')
const holidaysForm = useForm({ holidays: [] })

function resetHolidayForm() {
	holidayForm.holiday_date = ''
	holidayForm.name = ''
}

function addHoliday() {
	holidayFormError.value = ''
	holidayError.value = ''
	const date = holidayForm.holiday_date ? String(holidayForm.holiday_date).trim() : ''
	const name = holidayForm.name ? String(holidayForm.name).trim() : ''
	if (!date) {
		holidayFormError.value = t('consultants.holidayDateRequired') || 'تاريخ الإجازة مطلوب.'
		return
	}
	const exists = holidays.value.some(h => String(h.holiday_date) === date)
	if (exists) {
		holidayFormError.value = t('consultants.holidayDateDuplicate') || 'تاريخ الإجازة مضاف بالفعل.'
		return
	}
	holidays.value.push({
		id: null,
		holiday_date: date,
		name,
		_key: `new-${Date.now()}-${Math.random().toString(16).slice(2)}`,
	})
	holidays.value.sort((a, b) => String(a.holiday_date).localeCompare(String(b.holiday_date)))
	resetHolidayForm()
}

function removeHoliday(idx) {
	holidayFormError.value = ''
	holidayError.value = ''
	holidays.value.splice(idx, 1)
}

function toHolidayPayload() {
	return holidays.value.map(h => ({
		holiday_date: h.holiday_date,
		name: h.name || '',
	}))
}

function saveHolidays() {
	holidayError.value = ''
	holidayFormError.value = ''
	holidaysForm.clearErrors()
	holidaysForm.holidays = toHolidayPayload()
	holidaysForm.put(route('admin.consultants.holidays.replace', consultant.id), {
		onSuccess: () => success(t('consultants.holidaysSavedSuccessfully') || 'تم حفظ الإجازات بنجاح'),
		onError: () => error(t('consultants.holidaysSaveFailed') || 'فشل حفظ الإجازات'),
		preserveScroll: true,
	})
}
</script>
