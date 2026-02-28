<script setup lang="ts">
import { computed } from 'vue';

interface FeatureItem {
  title: string;
  description: string;
  image?: string;
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

// الألوان الافتراضية للبطاقات
const defaultColors = [
  'from-[#FDEBD0] to-[#FFF5E6]',   // بيج فاتح
  'from-[#FFF5E6] to-[#FFE8CC]',   // برتقالي فاتح
  'from-[#FFE8CC] to-[#FFDDB3]',   // برتقالي
];

// البيانات الافتراضية
const defaultFeatures: FeatureItem[] = [
  {
    title: 'أدر عملك من لوحة تحكم متنقلة',
    description: 'تابع حجوزاتك، عملائك، وأرباحك من هاتفك المحمول بسهولة',
    image: '/images/phone-mockup-1.png',
    background_color: 'from-[#FDEBD0] to-[#FFF5E6]',
  },
  {
    title: 'أضف خدماتك وحدد أسعارك',
    description: 'دراسات جدوى، تحليل مالي، خطط أعمال - قدم خدماتك بأسعارك الخاصة',
    image: '/images/phone-mockup-2.png',
    background_color: 'from-[#FFF5E6] to-[#FFE8CC]',
  },
  {
    title: 'وسع نطاق عملك',
    description: 'عروض يحتاج، إشعارات فورية، محفظة إلكترونية، تقارير تفصيلية',
    image: '/images/phone-mockup-3.png',
    background_color: 'from-[#FFE8CC] to-[#FFDDB3]',
  },
];

const features = computed(() => props.section?.items || defaultFeatures);

// الحصول على لون البطاقة
const getCardColor = (feature: FeatureItem, index: number) => {
  return feature.background_color || defaultColors[index % defaultColors.length];
};
</script>

<template>
  <section id="mobile-app" class="relative w-full py-24 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-full mb-4">
          التطبيق
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-4">
          {{ section?.title || 'انضم كمستشار واستفد من منصتنا' }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-8">
          {{ section?.subtitle || 'وسع قاعدة عملائك وزد دخلك من خلال تقديم خدماتك الاستشارية عبر منصتنا الاحترافية' }}
        </p>

        <!-- App Store Buttons -->
        <div class="flex items-center justify-center gap-4 mb-16">
          <a 
            href="#" 
            class="inline-flex items-center gap-3 px-6 py-3 bg-black text-white rounded-xl hover:bg-gray-800 transition-colors"
          >
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
            </svg>
            <div class="text-right">
              <div class="text-xs">Download on the</div>
              <div class="text-sm font-semibold">App Store</div>
            </div>
          </a>

          <a 
            href="#" 
            class="inline-flex items-center gap-3 px-6 py-3 bg-black text-white rounded-xl hover:bg-gray-800 transition-colors"
          >
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.16,10.81C20.5,11.08 20.75,11.5 20.75,12C20.75,12.5 20.53,12.9 20.18,13.18L17.89,14.5L15.39,12L17.89,9.5L20.16,10.81M6.05,2.66L16.81,8.88L14.54,11.15L6.05,2.66Z"/>
            </svg>
            <div class="text-right">
              <div class="text-xs">GET IT ON</div>
              <div class="text-sm font-semibold">Google Play</div>
            </div>
          </a>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-12 items-start">
        <div
          v-for="(feature, index) in features"
          :key="index"
          class="flex flex-col items-center text-center"
        >
          <!-- Image Container -->
          <div class="relative w-full max-w-[320px] h-[400px] mb-8 rounded-3xl overflow-hidden shadow-2xl">
            <img 
              v-if="feature.image"
              :src="`/storage/${feature.image}`" 
              :alt="feature.title"
              class="w-full h-full object-cover"
            />
            <div 
              v-else
              class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center"
            >
              <span class="text-6xl">📱</span>
            </div>
          </div>
          
          <!-- Feature Card -->
          <div 
            class="bg-gradient-to-br rounded-2xl p-8 w-full"
            :class="getCardColor(feature, index)"
          >
            <h3 class="text-2xl font-bold text-gray-900 mb-3">
              {{ feature.title }}
            </h3>
            <p class="text-gray-700 leading-relaxed">
              {{ feature.description }}
            </p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
