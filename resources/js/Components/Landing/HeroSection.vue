<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

interface Props {
  section: {
    title?: string;
    subtitle?: string;
    description?: string;
    image?: string;
    content?: any;
    items?: any[];
  };
}

defineProps<Props>();
const { t } = useI18n();
</script>

<template>
  <!-- Light hero - image on left, text on right -->
  <section class="relative min-h-[70vh] flex items-center overflow-hidden bg-white">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(18,57,42,0.03),transparent_50%)]" aria-hidden="true" />

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <!-- Image on the left (order-2 in RTL = visual left) -->
        <div class="order-1 lg:order-2 flex justify-center">
          <div class="relative w-full max-w-md">
            <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-xl ring-1 ring-gray-100">
              <img
                v-if="section.image"
                :src="`/storage/${section.image}`"
                alt="استشارة مالية"
                class="w-full h-auto object-cover"
              />
              <img
                v-else
                src="/images/landing/hero-financial.png"
                alt="استشارة مالية ومحاسبية"
                class="w-full h-auto object-cover"
              />
            </div>
          </div>
        </div>
        <!-- Text on the right -->
        <div class="order-2 lg:order-1 text-center lg:text-right">
          <p class="text-brand-forest text-sm font-semibold uppercase tracking-wider mb-4">
            {{ t('landing.hero.badge') }}
          </p>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-brand-dark leading-[1.2] tracking-tight mb-6">
            {{ section.title || t('landing.hero.title') }}
          </h1>
          <p class="text-lg sm:text-xl text-gray-600 max-w-xl mx-auto lg:mx-0 mb-4 leading-relaxed">
            {{ section.subtitle || t('landing.hero.subtitle') }}
          </p>
          <p class="text-brand-muted text-sm mb-10">
            {{ t('landing.hero.tagline') }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
            <Link
              href="/register"
              class="btn-primary inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold rounded-xl transition-all shadow-md hover:shadow-lg"
            >
              {{ t('landing.hero.ctaBook') }}
              <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Link>
            <Link
              href="#services"
              class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-brand-500 border-2 border-brand-500 rounded-xl hover:bg-brand-50 transition-all"
            >
              {{ t('landing.hero.ctaExplore') }}
            </Link>
          </div>
          <div class="mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-gray-600 text-sm">
            <span class="flex items-center gap-2">
              <span class="font-bold text-brand-500">+50</span>
              {{ t('landing.hero.advisorsCount') }}
            </span>
            <span class="flex items-center gap-1.5">
              <svg class="w-5 h-5 text-brand-forest" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="font-semibold text-brand-dark">4.9</span>
              {{ t('landing.hero.rating') }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.btn-primary {
  background-color: var(--color-brand-500);
  color: var(--color-white);
}
.btn-primary:hover {
  background-color: #0f2f23;
}
</style>
