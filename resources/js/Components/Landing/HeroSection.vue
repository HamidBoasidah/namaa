<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

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
</script>

<template>
  <!-- Light hero - white background -->
  <section class="relative min-h-[85vh] flex items-center overflow-hidden bg-white">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(18,57,42,0.03),transparent_50%)]" aria-hidden="true" />

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <div class="text-center lg:text-right order-2 lg:order-1">
          <p class="text-brand-forest text-sm font-semibold uppercase tracking-wider mb-4">
            استشارات مالية ومحاسبية
          </p>
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-brand-dark leading-[1.2] tracking-tight mb-6">
            {{ section.title || 'خُذ قراراتك بثقة مع خبراء مال وأعمال بجانبك دائمًا' }}
          </h1>
          <p class="text-lg sm:text-xl text-gray-600 max-w-xl mx-auto lg:mx-0 mb-4 leading-relaxed">
            {{ section.subtitle || 'من تحليل القوائم المالية إلى دراسة الجدوى وخطط النمو، كل ما تحتاجه متاح في مكان واحد.' }}
          </p>
          <p class="text-brand-muted text-sm mb-10">
            مستشارون معتمدون • جلسات مباشرة • تقارير احترافية
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
            <Link
              href="/register"
              class="btn-primary inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold rounded-xl transition-all shadow-md hover:shadow-lg"
            >
              طلب استشارة
              <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Link>
            <Link
              href="#services"
              class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-brand-500 border-2 border-brand-500 rounded-xl hover:bg-brand-50 transition-all"
            >
              استكشف خدماتنا
            </Link>
          </div>
          <div class="mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-gray-600 text-sm">
            <span class="flex items-center gap-2">
              <span class="font-bold text-brand-500">+50</span>
              مستشار معتمد
            </span>
            <span class="flex items-center gap-1.5">
              <svg class="w-5 h-5 text-brand-forest" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="font-semibold text-brand-dark">4.9</span>
              تقييم المنصة
            </span>
          </div>
        </div>

        <div class="order-1 lg:order-2 flex justify-center">
          <div class="relative">
            <div class="absolute -inset-4 bg-brand-pale/20 rounded-2xl blur-2xl" />
            <div class="relative aspect-[4/3] max-w-md w-full rounded-2xl overflow-hidden border border-gray-200 bg-white shadow-xl ring-1 ring-gray-100">
              <img
                v-if="section.image"
                :src="`/storage/${section.image}`"
                alt="استشارة مالية"
                class="w-full h-full object-cover"
              />
              <img
                v-else
                src="/images/landing/hero-financial.png"
                alt="استشارة مالية"
                class="w-full h-full object-cover"
              />
              <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-brand-dark/90 to-transparent p-4 flex items-center justify-between gap-3">
                <div>
                  <p class="text-white font-semibold">جلسة استشارية مباشرة</p>
                  <p class="text-white/90 text-sm">فيديو • صوت • محادثة</p>
                </div>
                <img src="/images/logo/logo-icon.png" alt="نماء" class="h-10 w-10 opacity-95 shrink-0 brightness-0 invert" />
              </div>
            </div>
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
