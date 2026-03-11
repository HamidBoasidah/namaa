<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Props {
  section: {
    title?: string;
    subtitle?: string;
    items?: Array<{
      title: string;
      description: string;
      icon: string;
    }>;
  };
}

const props = defineProps<Props>();

const defaultIcons = ['search', 'video', 'document', 'folder', 'shield', 'star'];

const features = computed(() => {
  if (props.section.items?.length) return props.section.items;
  return defaultIcons.map((icon, i) => ({
    title: t(`landing.features.items.${i}.title`),
    description: t(`landing.features.items.${i}.description`),
    icon,
  }));
});

const getIconPath = (iconName: string) => {
  const paths: Record<string, string> = {
    search: 'M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z',
    video: 'M15 10L19.553 7.724C20.6 7.199 22 7.952 22 9.157V14.843C22 16.048 20.6 16.801 19.553 16.276L15 14M5 18H12C13.657 18 15 16.657 15 15V9C15 7.343 13.657 6 12 6H5C3.343 6 2 7.343 2 9V15C2 16.657 3.343 18 5 18Z',
    document: 'M9 12H15M9 16H15M17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3H12.5858C12.851 3 13.1054 3.10536 13.2929 3.29289L18.7071 8.70711C18.8946 8.89464 19 9.149 19 9.41421V19C19 20.1046 18.1046 21 17 21Z',
    folder: 'M3 7C3 5.89543 3.89543 5 5 5H9.58579C9.851 5 10.1054 5.10536 10.2929 5.29289L12 7H19C20.1046 7 21 7.89543 21 9V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V7Z',
    shield: 'M9 12L11 14L15 10M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    star: 'M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z',
  };
  return paths[iconName] || paths.search;
};
</script>

<template>
  <!-- Light section: features -->
  <section id="features" class="relative py-20 lg:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-14">
        <h2 class="text-3xl sm:text-4xl font-bold text-brand-dark mb-3">
          {{ section.title || t('landing.features.title') }}
        </h2>
        <p class="text-lg text-gray-600 mb-2">
          {{ section.subtitle || t('landing.features.subtitle') }}
        </p>
        <p class="text-brand-muted font-medium">
          {{ t('landing.features.tagline') }}
        </p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <article
          v-for="(feature, index) in features"
          :key="index"
          class="feature-card rounded-2xl border border-gray-200 bg-white p-6 lg:p-8 hover:bg-brand-50 hover:border-brand-200 transition-all duration-300"
        >
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-forest/15 text-brand-500 mb-5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getIconPath(feature.icon)" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-brand-dark mb-2">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed text-sm">
            {{ feature.description }}
          </p>
          <div
            v-if="index === 0"
            class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-brand-pale/30 text-brand-dark text-xs font-medium"
          >
            {{ t('landing.features.downloadApp') }}
          </div>
        </article>
      </div>
    </div>
  </section>
</template>
