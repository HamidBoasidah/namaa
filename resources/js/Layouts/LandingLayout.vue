<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const isMenuOpen = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};
</script>

<template>
  <div class="min-h-screen bg-white" dir="rtl">
    <!-- Top Navigation Bar -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white border-b border-gray-200 shadow-sm">
      <div class="max-w-[1312px] mx-auto px-16">
        <div class="flex items-center justify-between h-20">
          <!-- Right: Navigation Actions -->
          <div class="flex items-center gap-4">
            <Link
              href="/login"
              class="px-5 py-2 text-base font-medium text-gray-900 bg-white border border-[#BDD2D3] rounded-lg hover:bg-gray-50 transition-colors shadow-sm"
            >
              انضم كمستشار
            </Link>
            <Link
              href="/register"
              class="px-5 py-2 text-base font-medium text-white bg-[#205355] rounded-lg hover:bg-[#1a4344] transition-colors"
            >
              حمل التطبيق الآن
            </Link>
          </div>

          <!-- Center: Navigation Menu -->
          <nav class="flex items-center gap-2">
            <Link
              href="/"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              الصفحة الرئيسية
            </Link>
            <Link
              href="#features"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              المميزات
            </Link>
            <Link
              href="#how-it-works"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              كيف يعمل
            </Link>
            <Link
              href="#services"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              للمستشارين
            </Link>
            <Link
              href="#contact"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              خدماتنا
            </Link>
            <Link
              href="#faq"
              class="px-3 py-2 text-base font-medium text-gray-600 hover:text-gray-900 rounded transition-colors"
            >
              الأسئلة الشائعة
            </Link>
          </nav>

          <!-- Left: Logo -->
          <div class="flex items-center">
            <Link href="/" class="flex items-center gap-2">
              <span class="text-2xl font-bold text-[#205355]">نماء الأعمال</span>
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content with top padding for fixed header -->
    <main class="pt-20">
      <slot />
    </main>
  </div>
</template>

<style scoped>
/* RTL Support */
[dir="rtl"] {
  direction: rtl;
}
</style>
