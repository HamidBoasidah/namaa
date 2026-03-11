<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { switchLocale } from '@/composables/useLocale';

const { t, locale } = useI18n();
const isMenuOpen = ref(false);
const isScrolled = ref(false);

const currentLocale = computed(() => (locale.value as string) || 'ar');

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value;
};

const handleScroll = () => {
  isScrolled.value = window.scrollY > 24;
};

async function toggleLocale() {
  const next = currentLocale.value === 'ar' ? 'en' : 'ar';
  await switchLocale(next);
}

onMounted(() => {
  document.documentElement.classList.remove('dark');
  window.addEventListener('scroll', handleScroll, { passive: true });
});
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});

const navLinks = [
  { href: '/', labelKey: 'landing.nav.home' },
  { href: '#features', labelKey: 'landing.nav.features' },
  { href: '#how-it-works', labelKey: 'landing.nav.howItWorks' },
  { href: '#services', labelKey: 'landing.nav.services' },
  { href: '#testimonials', labelKey: 'landing.nav.testimonials' },
  { href: '#faq', labelKey: 'landing.nav.faq' },
];
</script>

<template>
  <div class="landing-page min-h-screen landing-bg bg-white text-brand-dark" :dir="currentLocale === 'ar' ? 'rtl' : 'ltr'">
    <!-- Main Header - dark green -->
    <header
      class="fixed z-50 left-0 right-0 top-0 transition-all duration-300 bg-brand-500 backdrop-blur-md border-b border-white/10 shadow-md"
      :class="isScrolled ? 'shadow-lg' : ''"
    >
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-18">
          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="toggleLocale"
              class="inline-flex items-center justify-center w-9 h-9 text-xs font-medium text-white/90 bg-white/10 rounded-lg hover:bg-white/20 transition-colors"
              :title="currentLocale === 'ar' ? t('landing.nav.switchToEnglish') : t('landing.nav.switchToArabic')"
            >
              {{ currentLocale === 'ar' ? 'EN' : 'ع' }}
            </button>
            <Link href="/" class="flex items-center gap-2 shrink-0">
              <img src="/images/logo/logo-euro.png" alt="كسب" class="h-8 lg:h-9 w-auto header-logo" />
            </Link>
          </div>

          <nav class="hidden lg:flex items-center gap-0">
            <Link
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="px-4 py-2 text-sm font-medium text-white/90 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
            >
              {{ t(link.labelKey) }}
            </Link>
          </nav>

          <div class="hidden lg:flex items-center gap-3">
            <Link
              href="/login"
              class="px-4 py-2.5 text-sm font-semibold text-white/90 hover:text-white transition-colors"
            >
              {{ t('landing.nav.joinAsAdvisor') }}
            </Link>
            <Link
              href="/register"
              class="px-5 py-2.5 text-sm font-semibold text-brand-500 bg-white rounded-lg hover:bg-gray-100 transition-colors"
            >
              {{ t('landing.nav.downloadApp') }}
            </Link>
          </div>

          <button
            type="button"
            class="lg:hidden p-2 rounded-lg text-white hover:bg-white/10 transition-colors"
            :aria-label="t('menu.menu')"
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
          class="lg:hidden border-t border-white/20 bg-brand-500"
        >
          <div class="max-w-6xl mx-auto px-4 py-4 space-y-1">
            <Link
              v-for="link in navLinks"
              :key="link.href"
              :href="link.href"
              class="block px-4 py-3 text-base font-medium text-white hover:bg-white/10 rounded-lg transition-colors"
              @click="isMenuOpen = false"
            >
              {{ t(link.labelKey) }}
            </Link>
            <div class="pt-4 flex flex-col gap-2">
              <Link href="/login" class="text-center px-4 py-3 font-semibold text-white border border-white/50 rounded-lg hover:bg-white/10 transition-colors" @click="isMenuOpen = false">
                {{ t('landing.nav.joinAsAdvisor') }}
              </Link>
              <Link href="/register" class="text-center px-4 py-3 font-semibold text-brand-500 bg-white rounded-lg hover:bg-gray-100 transition-colors" @click="isMenuOpen = false">
                {{ t('landing.nav.downloadApp') }}
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
.header-logo {
  mix-blend-mode: screen;
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
