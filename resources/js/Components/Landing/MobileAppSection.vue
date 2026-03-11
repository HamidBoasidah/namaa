<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface FeatureItem {
  title: string;
  description: string;
  image?: string;
  icon?: string;
  background_color?: string;
}

interface Props {
  section?: {
    title?: string;
    subtitle?: string;
    content?: any;
    items?: FeatureItem[];
  };
}

const props = defineProps<Props>();

const defaultIcons = ['mobile', 'tag', 'chart-up'];

const features = computed(() => {
  if (props.section?.items?.length) return props.section.items;
  return defaultIcons.map((icon, i) => ({
    title: t(`landing.mobileApp.features.${i}.title`),
    description: t(`landing.mobileApp.features.${i}.description`),
    icon,
    background_color: 'from-brand-dark/5 to-white',
  }));
});

const getBg = (f: FeatureItem, i: number) =>
  f.background_color || 'from-brand-dark/5 to-white';

const getFeatureIconPath = (iconName: string) => {
  const paths: Record<string, string> = {
    mobile: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
    tag: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
    'chart-up': 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
  };
  return paths[iconName] || paths.mobile;
};
</script>

<template>
  <section id="mobile-app" class="relative py-20 lg:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14">
        <img src="/images/logo/logo.png" alt="كسب" class="h-10 mx-auto mb-6 object-contain" />
        <h2 class="text-3xl sm:text-4xl font-bold text-brand-dark mb-3">
          {{ t('landing.mobileApp.title') }}
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto mb-8">
          {{ section?.title || t('landing.mobileApp.subtitle') }}
        </p>

        <div class="flex flex-wrap items-center justify-center gap-4">
          <a
            href="#"
            class="store-btn inline-flex items-center gap-3 px-6 py-3.5 rounded-xl font-medium text-sm transition-all hover:shadow-lg"
          >
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
            </svg>
            <span>App Store</span>
          </a>
          <a
            href="#"
            class="store-btn inline-flex items-center gap-3 px-6 py-3.5 rounded-xl font-medium text-sm transition-all hover:shadow-lg"
          >
            <svg class="w-7 h-7" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
            </svg>
            <span>Google Play</span>
          </a>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-6 lg:gap-8">
        <article
          v-for="(feature, index) in features"
          :key="index"
          class="rounded-2xl border border-gray-200 bg-white p-6 lg:p-8 hover:border-brand-200 hover:shadow-md transition-all"
        >
          <div
            v-if="feature.image"
            class="aspect-[3/4] max-w-[180px] mx-auto mb-5 rounded-xl overflow-hidden bg-brand-50"
          >
            <img :src="`/storage/${feature.image}`" :alt="feature.title" class="w-full h-full object-cover" />
          </div>
          <div
            v-else
            class="w-16 h-16 mx-auto mb-5 rounded-xl flex items-center justify-center bg-brand-forest/15 text-brand-500"
          >
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" :d="getFeatureIconPath(feature.icon || 'mobile')" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-brand-dark mb-2 text-center">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed text-sm text-center">
            {{ feature.description }}
          </p>
        </article>
      </div>
    </div>
  </section>
</template>

<style scoped>
.store-btn {
  background-color: var(--color-brand-500);
  color: var(--color-white);
}
.store-btn:hover {
  background-color: #0f2f23;
}
</style>
