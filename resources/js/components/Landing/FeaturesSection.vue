<script setup lang="ts">
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

const defaultFeatures = [
  {
    title: 'ابحث عن المستشار المناسب لمشروعك',
    description: 'ابحث عن المستشارين المعتمدين واختر الأنسب حسب تخصصك واحتياجات عملك',
    icon: 'search',
  },
  {
    title: 'احجز جلسات استشارية عبر الإنترنت',
    description: 'جلسات مباشرة عبر الفيديو أو الصوت أو المحادثة النصية حسب راحتك',
    icon: 'video',
  },
  {
    title: 'اطلب خدمات جاهزة ومتخصصة',
    description: 'دراسات جدوى، تحليل مالي، خطط أعمال، إعادة هيكلة الديون، وتقييم الأداء',
    icon: 'document',
  },
  {
    title: 'تابع استشاراتك من مكان واحد',
    description: 'إدارة سهلة لكل مواعيدك، سجلات الجلسات، والتقارير المستلمة.',
    icon: 'folder',
  },
  {
    title: 'مدفوعات آمنة وسياسة استرداد واضحة',
    description: 'تصفح مئات المستشارين المعتمدين واختر الأنسب حسب تخصصك واحتياجات عملك',
    icon: 'shield',
  },
  {
    title: 'قيّم المستشار وشارك تجربتك',
    description: 'امنح المستشار تقييمك، وساعد باقي رواد الأعمال باختيارهم الأنسب لاحتياجاتهم.',
    icon: 'star',
  },
];

const features = props.section.items || defaultFeatures;

const getIcon = (iconName: string) => {
  const icons: Record<string, string> = {
    search: 'M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z',
    video: 'M15 10L19.553 7.724C20.6 7.199 22 7.952 22 9.157V14.843C22 16.048 20.6 16.801 19.553 16.276L15 14M5 18H12C13.657 18 15 16.657 15 15V9C15 7.343 13.657 6 12 6H5C3.343 6 2 7.343 2 9V15C2 16.657 3.343 18 5 18Z',
    document: 'M9 12H15M9 16H15M17 21H7C5.89543 21 5 20.1046 5 19V5C5 3.89543 5.89543 3 7 3H12.5858C12.851 3 13.1054 3.10536 13.2929 3.29289L18.7071 8.70711C18.8946 8.89464 19 9.149 19 9.41421V19C19 20.1046 18.1046 21 17 21Z',
    folder: 'M3 7C3 5.89543 3.89543 5 5 5H9.58579C9.851 5 10.1054 5.10536 10.2929 5.29289L12 7H19C20.1046 7 21 7.89543 21 9V18C21 19.1046 20.1046 20 19 20H5C3.89543 20 3 19.1046 3 18V7Z',
    shield: 'M9 12L11 14L15 10M20.618 5.984C20.196 5.732 19.489 5.379 18.651 5.042C17.814 4.705 16.914 4.402 16.169 4.227C15.423 4.052 14.9 4.016 14.581 4.001C14.263 3.986 14.169 4 14 4C13.831 4 13.737 3.986 13.419 4.001C13.1 4.016 12.577 4.052 11.831 4.227C11.086 4.402 10.186 4.705 9.349 5.042C8.511 5.379 7.804 5.732 7.382 5.984C7.275 6.046 7.168 6.108 7.062 6.171C6.388 6.556 6 7.255 6 8V12C6 13.864 6.431 15.372 7.119 16.634C7.808 17.896 8.756 18.917 9.765 19.753C10.774 20.589 11.849 21.246 12.65 21.66C13.051 21.867 13.371 22.012 13.591 22.105C13.701 22.151 13.783 22.183 13.836 22.203C13.863 22.213 13.881 22.219 13.891 22.223C13.896 22.225 13.899 22.226 13.9 22.227C13.967 22.257 14.033 22.257 14.1 22.227C14.101 22.226 14.104 22.225 14.109 22.223C14.119 22.219 14.137 22.213 14.164 22.203C14.217 22.183 14.299 22.151 14.409 22.105C14.629 22.012 14.949 21.867 15.35 21.66C16.151 21.246 17.226 20.589 18.235 19.753C19.244 18.917 20.192 17.896 20.881 16.634C21.569 15.372 22 13.864 22 12V8C22 7.255 21.612 6.556 20.938 6.171C20.832 6.108 20.725 6.046 20.618 5.984Z',
    star: 'M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z',
  };
  return icons[iconName] || icons.search;
};
</script>

<template>
  <section id="features" class="relative w-full py-24 bg-gradient-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-full mb-4">
          المميزات
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-4">
          {{ section.title || 'لماذا تختار منصتنا؟' }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          {{ section.subtitle || 'نوفر لك كل ما تحتاجه للحصول على استشارات مالية ومحاسبية احترافية بسهولة وأمان' }}
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="(feature, index) in features"
          :key="index"
          class="group relative bg-white rounded-2xl border border-gray-200 p-8 hover:shadow-xl hover:border-[#205355] transition-all duration-300"
        >
          <!-- Icon -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center justify-center w-16 h-16 bg-[#EAF1F1] rounded-2xl group-hover:bg-[#205355] transition-colors">
              <svg 
                class="w-8 h-8 text-[#205355] group-hover:text-white transition-colors" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path 
                  stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  :d="getIcon(feature.icon)" 
                />
              </svg>
            </div>
            <div 
              v-if="index === 0" 
              class="px-4 py-1.5 bg-[#D1FAE5] text-[#059669] text-sm font-medium rounded-full"
            >
              حمل التطبيق الآن
            </div>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-gray-900 mb-3">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600 leading-relaxed">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>
