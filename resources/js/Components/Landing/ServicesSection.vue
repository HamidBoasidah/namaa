<script setup lang="ts">
import { computed } from 'vue';

interface ServiceItem {
  title: string;
  description: string;
  icon?: string;
  image?: string;
  background_color?: string;
  link?: string;
  link_text?: string;
}

interface Props {
  section: {
    title?: string;
    subtitle?: string;
    items?: ServiceItem[];
  };
}

const props = defineProps<Props>();

const defaultServices: ServiceItem[] = [
  { title: 'تحليل مالي', description: 'تقدير التكاليف والإيرادات والأرباح المتوقعة، نقطة التعادل والعائد على الاستثمار.', icon: 'chart', link: '#', link_text: 'المزيد' },
  { title: 'تحليل سوق', description: 'دراسة حجم السوق، العملاء، المنافسين وفرص النمو والتهديدات.', icon: 'search', link: '#', link_text: 'المزيد' },
  { title: 'دراسة جدوى', description: 'تحليل الجدوى الاقتصادية والفنية للمشروع واتخاذ القرار المناسب.', icon: 'lightbulb', link: '#', link_text: 'المزيد' },
  { title: 'تقييم أعمال', description: 'فحص أداء الشركة وتحديد قيمتها السوقية وجاذبيتها للاستثمار.', icon: 'check', link: '#', link_text: 'المزيد' },
  { title: 'إدارة استثمار', description: 'مراقبة قرارات الاستثمار وتوزيع رأس المال وتقليل المخاطر.', icon: 'briefcase', link: '#', link_text: 'المزيد' },
  { title: 'خطة عمل', description: 'وثيقة توضح أهداف المشروع والخطوات المالية والتسويقية.', icon: 'document', link: '#', link_text: 'المزيد' },
];

const services = computed(() => props.section.items || defaultServices);

const getServiceIconPath = (iconName: string) => {
  const paths: Record<string, string> = {
    chart: 'M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z',
    search: 'M15.5 14h-.79l-.28-.27A6.471 6.471 0 0016 9.5 6.5 6.5 0 109.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z',
    lightbulb: 'M9 21c0 .55.45 1 1 1h4c.55 0 1-.45 1-1v-1H9v1zm3-19C8.14 2 5 5.14 5 9c0 2.38 1.19 4.47 3 5.74V17c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-2.26c1.81-1.27 3-3.36 3-5.74C19 5.14 15.86 2 12 2z',
    check: 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z',
    briefcase: 'M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z',
    document: 'M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z',
  };
  return paths[iconName] || paths.document;
};
</script>

<template>
  <!-- Light section: services -->
  <section id="services" class="relative py-20 lg:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-14">
        <h2 class="text-3xl sm:text-4xl font-bold text-brand-dark mb-3">
          خدماتنا
        </h2>
        <h3 class="text-xl text-gray-600 font-medium mb-2">
          {{ section.title || 'حلولنا التي تدفع مشاريعك نحو النجاح' }}
        </h3>
        <p class="text-brand-muted">
          {{ section.subtitle || 'خدمات مالية ومحاسبية مصممة لدعم نمو أعمالك' }}
        </p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <a
          v-for="(service, index) in services"
          :key="index"
          :href="service.link || '#'"
          class="group block rounded-2xl border border-gray-200 bg-brand-offwhite/50 p-6 lg:p-8 hover:bg-brand-50 hover:border-brand-200 transition-all duration-300 min-h-[260px] flex flex-col focus-visible:outline focus-visible:ring-2 focus-visible:ring-brand-500/30 focus-visible:ring-offset-2 focus-visible:ring-offset-white"
        >
          <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-forest/15 text-brand-500 mb-4">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
              <path :d="getServiceIconPath(typeof service.icon === 'string' && ['chart','search','lightbulb','check','briefcase','document'].includes(service.icon) ? service.icon : 'document')" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-brand-dark mb-3">
            {{ service.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed text-sm flex-1 line-clamp-3 mb-4">
            {{ service.description }}
          </p>
          <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand-accent group-hover:gap-2 transition-all">
            {{ service.link_text || 'المزيد' }}
            <svg class="w-4 h-4 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
          </span>
        </a>
      </div>
    </div>
  </section>
</template>
