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
  <!-- Reyiada-style hero: big stat/headline + tagline + dual CTAs -->
  <section class="relative min-h-[85vh] flex items-center overflow-hidden bg-gradient-to-b from-stone-50 to-white">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_-10%,rgba(20,184,166,0.08),transparent)]" />

    <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        <div class="text-center lg:text-right order-2 lg:order-1">
          <!-- Main headline - Reyiada style: bold statement -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-stone-900 leading-[1.2] tracking-tight mb-6">
            {{ section.title || 'خُذ قراراتك بثقة مع خبراء مال وأعمال بجانبك' }}
          </h1>
          <!-- Tagline under headline -->
          <p class="text-lg sm:text-xl text-stone-600 max-w-xl mx-auto lg:mx-0 mb-4 leading-relaxed">
            {{ section.subtitle || 'من تحليل القوائم المالية إلى دراسة الجدوى وخطط النمو، كل ما تحتاجه في مكان واحد.' }}
          </p>
          <!-- Optional stat line (Reyiada: "أكثر من 12 مليار دولار...") -->
          <p class="text-stone-500 text-sm mb-10">
            مستشارون معتمدون • جلسات مباشرة • تقارير احترافية
          </p>
          <!-- Dual CTAs - Reyiada: "طلب خدمة" + "استكشف خدماتنا" -->
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
            <Link
              href="/register"
              class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition-colors shadow-lg shadow-teal-900/20"
            >
              طلب استشارة
              <svg class="w-5 h-5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
              </svg>
            </Link>
            <Link
              href="#services"
              class="inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-stone-700 bg-white border-2 border-stone-200 rounded-lg hover:border-stone-300 hover:bg-stone-50 transition-colors"
            >
              استكشف خدماتنا
            </Link>
          </div>
          <!-- Trust line -->
          <div class="mt-12 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-stone-500 text-sm">
            <span class="flex items-center gap-2">
              <span class="font-bold text-stone-700">+50</span>
              مستشار معتمد
            </span>
            <span class="flex items-center gap-1.5">
              <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              <span class="font-semibold text-stone-700">4.9</span>
              تقييم المنصة
            </span>
          </div>
        </div>

        <div class="order-1 lg:order-2 flex justify-center">
          <div class="relative">
            <div class="absolute -inset-4 bg-gradient-to-br from-teal-100/50 to-amber-100/30 rounded-2xl blur-2xl" />
            <div class="relative aspect-[4/3] max-w-md w-full rounded-2xl overflow-hidden border border-stone-200 bg-stone-100 shadow-xl">
              <img
                v-if="section.image"
                :src="`/storage/${section.image}`"
                alt="استشارة مالية"
                class="w-full h-full object-cover"
              />
              <img
                v-else
                src="/images/landing/hero-consultant.jpg"
                alt="استشارة مالية"
                class="w-full h-full object-cover"
              />
              <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-4 flex items-center justify-between gap-3">
                <div>
                  <p class="text-white font-semibold">جلسة استشارية مباشرة</p>
                  <p class="text-white/80 text-sm">فيديو • صوت • محادثة</p>
                </div>
                <img src="/images/logo/logo-icon.svg" alt="نماء" class="h-10 w-10 opacity-90 shrink-0" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
