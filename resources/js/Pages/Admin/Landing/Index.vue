<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { route } from '@/route';
import AdminLayout from '@/components/layout/AdminLayout.vue';

interface LandingPage {
  id: number;
  title: string;
  slug: string;
  is_active: boolean;
  created_at: string;
}

interface Props {
  pages: LandingPage[];
}

const props = defineProps<Props>();
</script>

<template>
  <AdminLayout>
    <Head title="إدارة الصفحات" />

    <div class="p-8">
      <!-- Header -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            إدارة الصفحات
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-2">
            إدارة محتوى صفحات الموقع
          </p>
        </div>
      </div>

      <!-- Pages Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link
          v-for="page in pages"
          :key="page.id"
          :href="route('admin.landing.show', page.id)"
          class="block bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 hover:shadow-lg transition-shadow overflow-hidden group"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="w-12 h-12 bg-gradient-to-br from-[#205355] to-[#2B6F71] rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
              <span
                class="px-3 py-1 rounded-full text-xs font-medium"
                :class="page.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
              >
                {{ page.is_active ? 'نشط' : 'غير نشط' }}
              </span>
            </div>

            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-[#205355] transition-colors">
              {{ page.title }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
              {{ page.slug }}
            </p>

            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="text-xs text-gray-500">
                {{ new Date(page.created_at).toLocaleDateString('ar') }}
              </div>
              <div class="text-[#205355] group-hover:translate-x-1 transition-transform">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
              </div>
            </div>
          </div>
        </Link>
      </div>
    </div>
  </AdminLayout>
</template>
