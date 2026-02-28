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

// اللون الافتراضي للبطاقات حسب الترتيب
const defaultColors = [
  'bg-[#FFE4E6]',   // وردي فاتح
  'bg-[#F4E5FB]',   // بنفسجي فاتح
  'bg-[#D5F5F3]',   // تركواز فاتح
  'bg-[#FEF3C7]',   // أصفر فاتح
  'bg-[#E0F2FE]',   // أزرق فاتح
  'bg-[#FCE7F3]',   // وردي
];

const defaultServices: ServiceItem[] = [
  {
    title: 'تحليل مالي',
    description: 'تقدير التكاليف والإيرادات والأرباح المتوقعة للمشروع، وحساب مؤشرات مثل نقطة التعادل والعائد على الاستثمار.',
    background_color: 'bg-[#FFE4E6]',
    icon: '📊',
    link: '/services/financial-analysis',
    link_text: 'مزيد من المعلومات',
  },
  {
    title: 'تحليل سوق',
    description: 'دراسة حجم السوق، أنواع المصاف، سلوكهم، والمنافسين لاكتشاف فرص النمو والتهديدات.',
    background_color: 'bg-[#F4E5FB]',
    icon: '🔍',
    link: '/services/market-analysis',
    link_text: 'مزيد من المعلومات',
  },
  {
    title: 'دراسة جدوى',
    description: 'دراسة حجم السوق، أنواع العملاء، سلوكهم، والمنافسين لاكتشاف فرص النمو والتهديدات.',
    background_color: 'bg-[#D5F5F3]',
    icon: '💡',
    link: '/services/feasibility',
    link_text: 'مزيد من المعلومات',
  },
  {
    title: 'تقييم أعمال',
    description: 'فحص أداء ونمو ومردودية الشركة لتحديد قيمتها السوقية أو جاذبيتها للاستثمار أو البيع.',
    background_color: 'bg-[#FEF3C7]',
    icon: '✅',
    link: '/services/business-evaluation',
    link_text: 'مزيد من المعلومات',
  },
  {
    title: 'إدارة استثمار',
    description: 'مراقبة وتنظيم قرارات الاستثمار من اختيار الفرص وتوزيع رأس المال حتى مراقبة الأداء وتقليل المخاطر.',
    background_color: 'bg-[#E0F2FE]',
    icon: '💼',
    link: '/services/investment-management',
    link_text: 'مزيد من المعلومات',
  },
  {
    title: 'خطة عمل',
    description: 'وثيقة توضح أهداف المشروع، وخطوات التشغيل، والموارد المطلوبة، والخطة المالية والتسويقية لفترة زمنية محددة.',
    background_color: 'bg-[#FCE7F3]',
    icon: '📋',
    link: '/services/business-plan',
    link_text: 'مزيد من المعلومات',
  },
];

const services = computed(() => props.section.items || defaultServices);

// الحصول على لون البطاقة
const getCardColor = (service: ServiceItem, index: number) => {
  return service.background_color || defaultColors[index % defaultColors.length];
};
</script>

<template>
  <section id="services" class="relative w-full py-24 bg-white">
    <div class="max-w-7xl mx-auto px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-full mb-4">
          الخدمات
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-4">
          {{ section.title || 'خدمات متكاملة تُتقي مشروعك' }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          {{ section.subtitle || 'مجموعة متنوعة من الخدمات المالية والمحاسبية المصممة لدعم نمو أعمالك' }}
        </p>
      </div>

      <!-- Services Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="(service, index) in services"
          :key="index"
          class="group relative rounded-3xl overflow-hidden transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 h-[320px]"
          :class="getCardColor(service, index)"
        >
          <div class="p-6 h-full flex flex-col">
            <!-- المحتوى النصي في الأعلى -->
            <div class="text-right mb-auto">
              <h3 class="text-xl font-bold text-gray-900 mb-3">
                {{ service.title }}
              </h3>
              <p class="text-gray-700 leading-relaxed text-sm line-clamp-3">
                {{ service.description }}
              </p>
            </div>
            <!-- الأيقونة والزر في الأسفل -->
            <div class="flex items-end justify-between gap-4 mt-4">
              <a
                :href="service.link || '#'"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-900 text-white text-xs font-medium rounded-full hover:bg-gray-800 transition-all whitespace-nowrap"
              >
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span>{{ service.link_text || 'مزيد من المعلومات' }}</span>
              </a>
              <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center">
                <img 
                  v-if="service.image"
                  :src="service.image" 
                  :alt="service.title"
                  class="w-full h-full object-contain"
                />
                <span v-else class="text-6xl">{{ service.icon || '📄' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
