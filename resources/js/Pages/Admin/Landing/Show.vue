<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { route } from '@/route';
import AdminLayout from '@/components/layout/AdminLayout.vue';
import Modal from '@/components/profile/Modal.vue';
import DangerAlert from '@/components/modals/DangerAlert.vue';

interface SectionItem {
  id: number;
  title: string | null;
  subtitle: string | null;
  description: string | null;
  image: string | null;
  icon: string | null;
  background_color: string | null;
  order: number;
  is_active: boolean;
}

interface Section {
  id: number;
  type: string;
  title: string | null;
  subtitle: string | null;
  description: string | null;
  image: string | null;
  settings: Record<string, any> | null;
  order: number;
  is_active: boolean;
  items: SectionItem[];
}

interface Props {
  page: {
    id: number;
    title: string;
    slug: string;
  };
  sections: Section[];
}

const props = defineProps<Props>();

const selectedSection = ref<Section | null>(null);
const selectedItem = ref<SectionItem | null>(null);
const showSectionModal = ref(false);
const showItemModal = ref(false);
const currentSectionId = ref<number | null>(null);
const isDeleteModalOpen = ref(false);
const itemToDelete = ref<{ id: number; title: string } | null>(null);

const sectionForm = useForm({
  title: '',
  subtitle: '',
  description: '',
  image: null as File | null,
  is_active: true,
  settings: {} as Record<string, any>,
});

const currentImageUrl = ref<string | null>(null);
const imagePreview = ref<string | null>(null);

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    sectionForm.image = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const removeImage = () => {
  sectionForm.image = null;
  imagePreview.value = null;
  currentImageUrl.value = null;
};

const itemForm = useForm({
  title: '',
  subtitle: '',
  description: '',
  image: null as File | null,
  icon: '',
  background_color: '',
  is_active: true,
});

const currentItemImageUrl = ref<string | null>(null);
const itemImagePreview = ref<string | null>(null);

const handleItemImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const file = target.files?.[0];
  if (file) {
    itemForm.image = file;
    itemImagePreview.value = URL.createObjectURL(file);
  }
};

const removeItemImage = () => {
  itemForm.image = null;
  itemImagePreview.value = null;
  currentItemImageUrl.value = null;
};

const openSectionModal = (section: Section) => {
  selectedSection.value = section;
  currentSectionId.value = section.id;
  sectionForm.title = section.title || '';
  sectionForm.subtitle = section.subtitle || '';
  sectionForm.description = section.description || '';
  sectionForm.is_active = section.is_active;
  sectionForm.settings = (section as any).settings || {};
  currentImageUrl.value = section.image;
  imagePreview.value = null;
  sectionForm.image = null;
  showSectionModal.value = true;
};

const closeSectionModal = () => {
  showSectionModal.value = false;
  selectedSection.value = null;
  currentSectionId.value = null;
  currentImageUrl.value = null;
  imagePreview.value = null;
  sectionForm.reset();
};

const openItemModal = (item: SectionItem) => {
  selectedItem.value = item;
  itemForm.title = item.title || '';
  itemForm.subtitle = item.subtitle || '';
  itemForm.description = item.description || '';
  itemForm.icon = item.icon || '';
  itemForm.background_color = item.background_color || '';
  itemForm.is_active = item.is_active;
  currentItemImageUrl.value = item.image;
  itemImagePreview.value = null;
  itemForm.image = null;
  showItemModal.value = true;
};

const openNewItemModal = (sectionId: number) => {
  selectedItem.value = null;
  currentSectionId.value = sectionId;
  itemForm.reset();
  itemForm.is_active = true;
  currentItemImageUrl.value = null;
  itemImagePreview.value = null;
  showItemModal.value = true;
};

const closeItemModal = () => {
  showItemModal.value = false;
  selectedItem.value = null;
  currentItemImageUrl.value = null;
  itemImagePreview.value = null;
  itemForm.reset();
};

const updateSection = () => {
  if (!currentSectionId.value) return;
  
  // Convert boolean to number for FormData and serialize settings
  const formData = {
    ...sectionForm.data(),
    is_active: sectionForm.is_active ? 1 : 0,
    settings: JSON.stringify(sectionForm.settings),
  };
  
  sectionForm.transform(() => formData).post(route('admin.landing.sections.update', currentSectionId.value), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeSectionModal();
    },
    headers: {
      'X-HTTP-Method-Override': 'PUT'
    }
  });
};

const updateItem = () => {
  if (!selectedItem.value) return;
  
  // Convert boolean to number for FormData
  const formData = {
    ...itemForm.data(),
    is_active: itemForm.is_active ? 1 : 0,
  };
  
  itemForm.transform(() => formData).post(route('admin.landing.items.update', selectedItem.value.id), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeItemModal();
    },
    headers: {
      'X-HTTP-Method-Override': 'PUT'
    }
  });
};

const createItem = () => {
  if (!currentSectionId.value) return;
  
  // Convert boolean to number for FormData
  const formData = {
    ...itemForm.data(),
    is_active: itemForm.is_active ? 1 : 0,
  };
  
  itemForm.transform(() => formData).post(route('admin.landing.items.store', currentSectionId.value), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      closeItemModal();
    },
  });
};

const submitItem = () => {
  if (selectedItem.value) {
    updateItem();
  } else {
    createItem();
  }
};

const openDeleteModal = (itemId: number, itemTitle: string) => {
  itemToDelete.value = { id: itemId, title: itemTitle };
  isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
  isDeleteModalOpen.value = false;
  itemToDelete.value = null;
};

const confirmDelete = () => {
  if (itemToDelete.value) {
    router.delete(route('admin.landing.items.delete', itemToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        closeDeleteModal();
      },
      onError: () => {
        closeDeleteModal();
      },
    });
  }
};

const getSectionTypeName = (type: string) => {
  const types: Record<string, string> = {
    hero: 'القسم الرئيسي',
    features: 'المميزات',
    how_it_works: 'كيف يعمل',
    services: 'الخدمات',
    mobile_app: 'تطبيق الموبايل',
    testimonials: 'آراء العملاء',
    faq: 'الأسئلة الشائعة',
  };
  return types[type] || type;
};
</script>

<template>
  <AdminLayout>
    <Head :title="'إدارة ' + page.title" />

    <div class="p-8">
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ page.title }}
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            إدارة محتوى الصفحة الرئيسية وأقسامها
          </p>
        </div>
        <div class="flex gap-3">
          <a
            :href="route('landing.index')"
            target="_blank"
            class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors flex items-center gap-2"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            معاينة الصفحة
          </a>
        </div>
      </div>

      <!-- Sections List -->
      <div class="space-y-6">
        <div
          v-for="section in sections"
          :key="section.id"
          class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden"
        >
          <!-- Section Header -->
          <div class="p-6 bg-gradient-to-r from-[#205355] to-[#2B6F71] text-white">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <span class="text-2xl">📄</span>
                  </div>
                  <div>
                    <h3 class="text-xl font-bold">
                      {{ getSectionTypeName(section.type) }}
                    </h3>
                    <p class="text-sm text-white/80">
                      {{ section.title }}
                    </p>
                  </div>
                </div>
                <span
                  class="px-3 py-1 rounded-full text-xs font-medium"
                  :class="section.is_active ? 'bg-green-500 text-white' : 'bg-gray-500 text-white'"
                >
                  {{ section.is_active ? 'نشط' : 'غير نشط' }}
                </span>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="openSectionModal(section)"
                  class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg transition-colors flex items-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                  تعديل القسم
                </button>
              </div>
            </div>
          </div>

          <!-- Section Content -->
          <div class="p-6">
            <!-- Section Image (if exists) -->
            <div v-if="section.image" class="mb-6">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                صورة القسم
              </label>
              <div class="relative inline-block">
                <img
                  :src="`/storage/${section.image}`"
                  alt="Section Image"
                  class="w-full max-w-md h-48 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  العنوان الرئيسي
                </label>
                <p class="text-gray-900 dark:text-white">
                  {{ section.title || '-' }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  العنوان الفرعي
                </label>
                <p class="text-gray-900 dark:text-white">
                  {{ section.subtitle || '-' }}
                </p>
              </div>
            </div>

            <!-- Section Items -->
            <div v-if="section.items && section.items.length > 0" class="mt-6">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                  عناصر القسم ({{ section.items.length }})
                </h4>
                <button
                  @click="openNewItemModal(section.id)"
                  class="px-4 py-2 bg-[#205355] text-white rounded-lg hover:bg-[#2B6F71] transition-colors flex items-center gap-2"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  إضافة عنصر
                </button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="item in section.items"
                  :key="item.id"
                  class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                >
                  <!-- Item Image (if exists) -->
                  <div v-if="item.image" class="mb-3">
                    <img
                      :src="`/storage/${item.image}`"
                      :alt="item.title || 'Item Image'"
                      class="w-full h-32 object-cover rounded-lg"
                    />
                  </div>

                  <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-2">
                      <span v-if="item.icon" class="text-2xl">{{ item.icon }}</span>
                      <span
                        class="px-2 py-1 rounded text-xs font-medium"
                        :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-300 text-gray-800'"
                      >
                        {{ item.is_active ? 'نشط' : 'معطل' }}
                      </span>
                    </div>
                    <div class="flex items-center gap-2">
                      <button
                        @click="openItemModal(item)"
                        class="text-gray-600 hover:text-[#205355] dark:text-gray-400 dark:hover:text-[#2B6F71] transition-colors"
                        title="تعديل"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                      </button>
                      <button
                        @click="openDeleteModal(item.id, item.title || item.subtitle || 'هذا العنصر')"
                        class="text-gray-600 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-500 transition-colors"
                        title="حذف"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                  <h5 class="font-semibold text-gray-900 dark:text-white mb-2">
                    {{ item.title || item.subtitle || 'بدون عنوان' }}
                  </h5>
                  <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                    {{ item.description || '-' }}
                  </p>
                </div>
              </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
              <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              <p class="mb-4">لا توجد عناصر في هذا القسم</p>
              <button
                @click="openNewItemModal(section.id)"
                class="px-4 py-2 bg-[#205355] text-white rounded-lg hover:bg-[#2B6F71] transition-colors inline-flex items-center gap-2"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                إضافة عنصر جديد
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Edit Modal -->
    <Modal v-if="showSectionModal" @close="closeSectionModal">
      <template #body>
        <div class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              تعديل القسم
            </h2>
            <button
              @click="closeSectionModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Section Type Badge -->
          <div v-if="selectedSection" class="mb-6">
            <span class="px-4 py-2 bg-gradient-to-r from-[#205355] to-[#2B6F71] text-white rounded-lg text-sm font-medium">
              {{ getSectionTypeName(selectedSection.type) }}
            </span>
          </div>

          <!-- Form -->
          <form @submit.prevent="updateSection" class="space-y-6">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                العنوان الرئيسي
              </label>
              <input
                v-model="sectionForm.title"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل العنوان الرئيسي"
              />
              <p v-if="sectionForm.errors.title" class="mt-2 text-sm text-red-600">
                {{ sectionForm.errors.title }}
              </p>
            </div>

            <!-- Subtitle -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                العنوان الفرعي
              </label>
              <input
                v-model="sectionForm.subtitle"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل العنوان الفرعي"
              />
              <p v-if="sectionForm.errors.subtitle" class="mt-2 text-sm text-red-600">
                {{ sectionForm.errors.subtitle }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                الوصف
              </label>
              <textarea
                v-model="sectionForm.description"
                rows="4"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل الوصف"
              ></textarea>
              <p v-if="sectionForm.errors.description" class="mt-2 text-sm text-red-600">
                {{ sectionForm.errors.description }}
              </p>
            </div>

            <!-- Image Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                صورة القسم
              </label>
              
              <!-- Current or Preview Image -->
              <div v-if="imagePreview || currentImageUrl" class="mb-4">
                <div class="relative inline-block">
                  <img
                    :src="imagePreview || (currentImageUrl ? `/storage/${currentImageUrl}` : '')"
                    alt="Section Image"
                    class="w-full max-w-md h-48 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600"
                  />
                  <button
                    type="button"
                    @click="removeImage"
                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Upload Button -->
              <div class="flex items-center gap-3">
                <label class="cursor-pointer">
                  <input
                    type="file"
                    @change="handleImageChange"
                    accept="image/*"
                    class="hidden"
                  />
                  <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ imagePreview || currentImageUrl ? 'تغيير الصورة' : 'رفع صورة' }}</span>
                  </div>
                </label>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  PNG, JPG, GIF حتى 10MB
                </span>
              </div>
              <p v-if="sectionForm.errors.image" class="mt-2 text-sm text-red-600">
                {{ sectionForm.errors.image }}
              </p>
            </div>

            <!-- Services Section Layout Option -->
            <div v-if="selectedSection?.type === 'services'" class="space-y-2">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                نمط عرض البطاقات
              </label>
              <div class="grid grid-cols-3 gap-3">
                <button
                  type="button"
                  @click="sectionForm.settings = { ...sectionForm.settings, layout: 'mixed' }"
                  class="p-4 rounded-lg border-2 transition-all text-center"
                  :class="(!sectionForm.settings?.layout || sectionForm.settings?.layout === 'mixed') 
                    ? 'border-[#205355] bg-[#205355]/10' 
                    : 'border-gray-300 dark:border-gray-600 hover:border-[#205355]/50'"
                >
                  <!-- Bento Grid: 3 أعمدة مع بطاقة ممتدة عمودياً -->
                  <div class="flex items-start justify-center gap-0.5">
                    <!-- العمودين الأولين: عاديين -->
                    <div class="flex flex-col gap-0.5">
                      <div class="w-4 h-5 bg-[#205355]/60 rounded"></div>
                      <div class="w-4 h-4 bg-[#205355]/40 rounded"></div>
                    </div>
                    <div class="flex flex-col gap-0.5">
                      <div class="w-4 h-5 bg-[#205355]/60 rounded"></div>
                      <div class="w-4 h-4 bg-[#205355]/40 rounded"></div>
                    </div>
                    <!-- العمود الثالث: ممتد عمودياً -->
                    <div class="w-4 h-10 bg-[#205355]/50 rounded"></div>
                  </div>
                  <span class="text-xs font-medium mt-2 block text-gray-700 dark:text-gray-300">Bento Grid</span>
                </button>
                <button
                  type="button"
                  @click="sectionForm.settings = { ...sectionForm.settings, layout: 'horizontal' }"
                  class="p-4 rounded-lg border-2 transition-all text-center"
                  :class="sectionForm.settings?.layout === 'horizontal' 
                    ? 'border-[#205355] bg-[#205355]/10' 
                    : 'border-gray-300 dark:border-gray-600 hover:border-[#205355]/50'"
                >
                  <div class="flex flex-col items-center gap-1">
                    <div class="w-16 h-4 bg-[#205355]/60 rounded"></div>
                    <div class="w-16 h-4 bg-[#205355]/60 rounded"></div>
                    <div class="w-16 h-4 bg-[#205355]/60 rounded"></div>
                  </div>
                  <span class="text-xs font-medium mt-2 block text-gray-700 dark:text-gray-300">أفقي</span>
                </button>
                <button
                  type="button"
                  @click="sectionForm.settings = { ...sectionForm.settings, layout: 'vertical' }"
                  class="p-4 rounded-lg border-2 transition-all text-center"
                  :class="sectionForm.settings?.layout === 'vertical' 
                    ? 'border-[#205355] bg-[#205355]/10' 
                    : 'border-gray-300 dark:border-gray-600 hover:border-[#205355]/50'"
                >
                  <div class="flex gap-1 justify-center">
                    <div class="w-5 h-10 bg-[#205355]/60 rounded"></div>
                    <div class="w-5 h-10 bg-[#205355]/60 rounded"></div>
                    <div class="w-5 h-10 bg-[#205355]/60 rounded"></div>
                  </div>
                  <span class="text-xs font-medium mt-2 block text-gray-700 dark:text-gray-300">عمودي</span>
                </button>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                اختر طريقة عرض بطاقات الخدمات: Bento Grid (تصميم متنوع)، أفقي فقط، أو عمودي فقط
              </p>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center gap-3">
              <input
                v-model="sectionForm.is_active"
                type="checkbox"
                id="section-active"
                class="w-5 h-5 text-[#205355] border-gray-300 rounded focus:ring-[#205355]"
              />
              <label for="section-active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                تفعيل القسم (سيظهر في الصفحة الرئيسية)
              </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                @click="closeSectionModal"
                class="px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              >
                إلغاء
              </button>
              <button
                type="submit"
                :disabled="sectionForm.processing"
                class="px-6 py-3 bg-gradient-to-r from-[#205355] to-[#2B6F71] text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center gap-2"
              >
                <svg v-if="sectionForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>حفظ التعديلات</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>

    <!-- Item Edit Modal -->
    <Modal v-if="showItemModal" @close="closeItemModal">
      <template #body>
        <div class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8">
          <!-- Modal Header -->
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
              {{ selectedItem ? 'تعديل العنصر' : 'إضافة عنصر جديد' }}
            </h2>
            <button
              @click="closeItemModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Form -->
          <form @submit.prevent="submitItem" class="space-y-6">
            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                العنوان
              </label>
              <input
                v-model="itemForm.title"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل العنوان"
              />
              <p v-if="itemForm.errors.title" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.title }}
              </p>
            </div>

            <!-- Subtitle -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                العنوان الفرعي
              </label>
              <input
                v-model="itemForm.subtitle"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل العنوان الفرعي"
              />
              <p v-if="itemForm.errors.subtitle" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.subtitle }}
              </p>
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                الوصف
              </label>
              <textarea
                v-model="itemForm.description"
                rows="4"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="أدخل الوصف"
              ></textarea>
              <p v-if="itemForm.errors.description" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.description }}
              </p>
            </div>

            <!-- Image Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                صورة العنصر
              </label>
              
              <!-- Current or Preview Image -->
              <div v-if="itemImagePreview || currentItemImageUrl" class="mb-4">
                <div class="relative inline-block">
                  <img
                    :src="itemImagePreview || (currentItemImageUrl ? `/storage/${currentItemImageUrl}` : '')"
                    alt="Item Image"
                    class="w-full max-w-md h-48 object-cover rounded-lg border-2 border-gray-300 dark:border-gray-600"
                  />
                  <button
                    type="button"
                    @click="removeItemImage"
                    class="absolute top-2 right-2 p-2 bg-red-500 text-white rounded-full hover:bg-red-600 transition-colors"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Upload Button -->
              <div class="flex items-center gap-3">
                <label class="cursor-pointer">
                  <input
                    type="file"
                    @change="handleItemImageChange"
                    accept="image/*"
                    class="hidden"
                  />
                  <div class="px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ itemImagePreview || currentItemImageUrl ? 'تغيير الصورة' : 'رفع صورة' }}</span>
                  </div>
                </label>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                  PNG, JPG, GIF حتى 10MB
                </span>
              </div>
              <p v-if="itemForm.errors.image" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.image }}
              </p>
            </div>

            <!-- Icon -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                الأيقونة (emoji)
              </label>
              <input
                v-model="itemForm.icon"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="مثال: 🎯"
              />
              <p v-if="itemForm.errors.icon" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.icon }}
              </p>
            </div>

            <!-- Background Color -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                لون الخلفية
              </label>
              <input
                v-model="itemForm.background_color"
                type="text"
                class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#205355] focus:border-transparent"
                placeholder="مثال: #E0F2FE"
              />
              <p v-if="itemForm.errors.background_color" class="mt-2 text-sm text-red-600">
                {{ itemForm.errors.background_color }}
              </p>
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center gap-3">
              <input
                v-model="itemForm.is_active"
                type="checkbox"
                id="item-active"
                class="w-5 h-5 text-[#205355] border-gray-300 rounded focus:ring-[#205355]"
              />
              <label for="item-active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                تفعيل العنصر (سيظهر في القسم)
              </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <button
                type="button"
                @click="closeItemModal"
                class="px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              >
                إلغاء
              </button>
              <button
                type="submit"
                :disabled="itemForm.processing"
                class="px-6 py-3 bg-gradient-to-r from-[#205355] to-[#2B6F71] text-white rounded-lg hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center gap-2"
              >
                <svg v-if="itemForm.processing" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>{{ selectedItem ? 'حفظ التعديلات' : 'إضافة' }}</span>
              </button>
            </div>
          </form>
        </div>
      </template>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <DangerAlert
      :isOpen="isDeleteModalOpen"
      title="هل أنت متأكد؟"
      :message="`هل تريد حذف '${itemToDelete?.title}'؟ هذا الإجراء لا يمكن التراجع عنه.`"
      @close="closeDeleteModal"
      @confirm="confirmDelete"
    />
  </AdminLayout>
</template>
