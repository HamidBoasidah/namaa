<script setup lang="ts">
interface Props {
  section: {
    title?: string;
    subtitle?: string;
    image?: string;
    items?: Array<{
      title: string;
      description: string;
      icon: string;
    }>;
  };
}

const props = defineProps<Props>();

const defaultSteps = [
  {
    title: 'تصميم المستشارين والخدمات',
    description: 'ابحث عن المستشارين حسب التخصص والتقييمات واختر الخدمة المناسبة',
    icon: 'check',
  },
  {
    title: 'اختر الوقت والطريقة وادفع بأمان',
    description: 'حدد موعد الجلسة ونوع الاستشارة (فيديو/صوت/نص)، وأتمم الدفع الآمن',
    icon: 'check',
  },
  {
    title: 'التق بالمستشار واستلم التقرير',
    description: 'احضر الجلسة الاستشارية، استلم التقارير والمخرجات، ثم قيّم الخدمة',
    icon: 'check',
  },
];

const steps = props.section.items || defaultSteps;
</script>

<template>
  <section id="how-it-works" class="relative w-full py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-7xl mx-auto px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <!-- Right Side: Steps -->
        <div class="order-2 lg:order-1">
          <div class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-full mb-4">
            كيف يعمل
          </div>
          <h2 class="text-4xl font-bold text-gray-900 mb-4">
            {{ section.title || 'ثلاث خطوات بسيطة' }}
          </h2>
          <p class="text-xl text-gray-600 mb-12">
            {{ section.subtitle || 'للحصول على الاستشارة التي تحتاجها' }}
          </p>

          <!-- Steps List -->
          <div class="space-y-8">
            <div
              v-for="(step, index) in steps"
              :key="index"
              class="flex gap-6"
            >
              <!-- Step Number with Line -->
              <div class="flex flex-col items-center">
                <div class="w-12 h-12 bg-[#205355] rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div 
                  v-if="index < steps.length - 1" 
                  class="w-0.5 h-full bg-gray-200 mt-2"
                ></div>
              </div>

              <!-- Step Content -->
              <div class="flex-1 pb-8">
                <h3 class="text-xl font-bold text-gray-900 mb-2">
                  {{ step.title }}
                </h3>
                <p class="text-gray-600 leading-relaxed">
                  {{ step.description }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Left Side: Mobile Mockup Image -->
        <div class="order-1 lg:order-2 relative flex justify-center lg:justify-start">
          <div class="relative">
            <!-- Geometric Background Shapes -->
            <div class="absolute inset-0 -z-10">
              <!-- Large Circle Background -->
              <div class="absolute -left-20 top-1/2 -translate-y-1/2 w-[400px] h-[400px] bg-gradient-to-br from-[#E6F7F7]/40 to-[#D1FAE5]/30 rounded-full blur-3xl"></div>
              
              <!-- Triangle Shape -->
              <div class="absolute -left-10 bottom-10 w-0 h-0 border-l-[100px] border-l-transparent border-b-[150px] border-b-[#205355]/20 border-r-[100px] border-r-transparent transform rotate-12"></div>
              
              <!-- Small Circles -->
              <div class="absolute top-10 -right-5 w-20 h-20 bg-[#4ECDC4]/20 rounded-full"></div>
              <div class="absolute bottom-20 right-10 w-12 h-12 bg-[#FF878A]/20 rounded-full"></div>
              
              <!-- Rectangles -->
              <div class="absolute top-1/4 -left-5 w-16 h-16 bg-gradient-to-br from-[#205355]/10 to-[#2B6F71]/20 rounded-lg transform rotate-45"></div>
            </div>

            <!-- Mobile Mockup Image -->
            <img 
              v-if="section.image"
              :src="`/storage/${section.image}`" 
              alt="ثلاث خطوات بسيطة"
              class="relative z-10 w-full max-w-[500px] h-auto object-contain drop-shadow-2xl"
            />
            <img 
              v-else
              src="/images/landing/how-it-works-mockup.png" 
              alt="ثلاث خطوات بسيطة"
              class="relative z-10 w-full max-w-[500px] h-auto object-contain drop-shadow-2xl"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
