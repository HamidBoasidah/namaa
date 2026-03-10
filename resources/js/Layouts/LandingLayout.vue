<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const isMenuOpen = ref(false);
const isScrolled = ref(false);

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 24;
};

onMounted(() => {
  document.documentElement.classList.remove('dark');
  window.addEventListener('scroll', handleScroll, { passive: true });
});
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const navLinks = [
  { href: '/', label: 'الرئيسية' },
  { href: '#features', label: 'المميزات' },
  { href: '#how-it-works', label: 'كيف يعمل' },
  { href: '#services', label: 'الخدمات' },
  { href: '#testimonials', label: 'آراء العملاء' },
  { href: '#faq', label: 'الأسئلة الشائعة' },
];
</script>

<template>
  <div class="landing-page min-h-screen landing-bg bg-brand-offwhite text-brand-dark" dir="rtl">
    <!-- Main Header - light, professional -->
    <header
      class="fixed z-50 left-0 right-0 top-0 transition-all duration-300 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm"
      :class="isScrolled ? 'shadow-md' : ''"
    >
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-18">
          <Link href="/" class="flex items-center gap-2 shrink-0">
            <img src="/images/logo/logo.png" alt="نماء الأعمال" class="h-8 lg:h-9 w-auto landing-logo" />
          </Link>

          <nav class="hidden lg:flex items-center gap-0">
            <Link
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-brand-500 hover:bg-brand-50 rounded-lg transition-colors"
            >
              {{ link.label }}
            </Link>
          </nav>

          <div class="hidden lg:flex items-center gap-3">
            <Link
              href="/login"
              class="px-4 py-2.5 text-sm font-semibold text-brand-500 hover:text-brand-600 transition-colors"
            >
              انضم كمستشار
            </Link>
            <Link
              href="/register"
              class="px-5 py-2.5 text-sm font-semibold text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors"
            >
              حمل التطبيق
            </Link>
          </div>

          <button
            type="button"
            class="lg:hidden p-2 rounded-lg text-brand-500 hover:bg-brand-50 transition-colors"
            aria-label="القائمة"
            @click="toggleMenu"
          >
            <svg v-if="!isMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0 -translate-y-2"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 -translate-y-2"
      >
        <div
          v-show="isMenuOpen"
          class="lg:hidden border-t border-gray-200 bg-white"
        >
          <div class="max-w-6xl mx-auto px-4 py-4 space-y-1">
            <Link
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="block px-4 py-3 text-base font-medium text-gray-700 hover:bg-brand-50 hover:text-brand-500 rounded-lg transition-colors"
              @click="isMenuOpen = false"
            >
              {{ link.label }}
            </Link>
            <div class="pt-4 flex flex-col gap-2">
              <Link href="/login" class="text-center px-4 py-3 font-semibold text-brand-500 border border-brand-500 rounded-lg hover:bg-brand-50 transition-colors" @click="isMenuOpen = false">
                انضم كمستشار
              </Link>
              <Link href="/register" class="text-center px-4 py-3 font-semibold text-white bg-brand-500 rounded-lg hover:bg-brand-600 transition-colors" @click="isMenuOpen = false">
                حمل التطبيق
              </Link>
            </div>
          </div>
        </div>
      </Transition>
    </header>

    <main class="pt-16 lg:pt-20">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.landing-logo {
  filter: none;
}
[dir="rtl"] {
  direction: rtl;
}

.landing-bg {
  position: relative;
}
.landing-bg::before {
  content: '';
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: radial-gradient(ellipse 80% 50% at 20% 20%, rgba(18, 57, 42, 0.03) 0%, transparent 50%),
    radial-gradient(ellipse 60% 40% at 80% 80%, rgba(47, 104, 79, 0.04) 0%, transparent 50%);
  animation: landing-bg-shift 18s ease-in-out infinite;
}
.landing-bg > * {
  position: relative;
  z-index: 1;
}
@keyframes landing-bg-shift {
  0%, 100% { opacity: 1; transform: scale(1) translate(0, 0); }
  50% { opacity: 0.95; transform: scale(1.01) translate(-0.5%, -0.25%); }
}
</style>
