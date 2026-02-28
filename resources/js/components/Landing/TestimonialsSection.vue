<script setup lang="ts">
import { ref } from 'vue';

interface Testimonial {
  name?: string;
  role?: string;
  rating?: number;
  text?: string;
  avatar?: string;
  image?: string;
  title?: string;
  subtitle?: string;
  description?: string;
}

interface Props {
  section?: {
    title?: string;
    subtitle?: string;
    items?: Testimonial[];
  };
}

const props = defineProps<Props>();

const defaultTestimonials: Testimonial[] = [
  {
    title: 'محمد السعيد',
    subtitle: 'مشغل أونجة البدنيل',
    rating: 5,
    description: '"المستشار كان محترفاً جداً وقدم لي نصائح عملية لتحسين التدفقات النقدية."',
    avatar: '👨‍💼',
  },
  {
    title: 'فاطمة أحمد',
    subtitle: 'صاحبة مشروع تجاري',
    rating: 5,
    description: '"جلسة الاستشارة التجارية وضحت لي نقاط ضعف المنافسين وزودتني بأفكار جديدة للتطوير."',
    avatar: '👩‍💼',
  },
  {
    title: 'خالد العتيبي',
    subtitle: 'رائد أعمال',
    rating: 5,
    description: '"حصلت على دراسة جدوى كاملة في وقت قياسي، ساعدتني كثيراً في إقناع المستثمرين."',
    avatar: '👨‍💻',
  },
];

const testimonials = props.section?.items?.length ? props.section.items : defaultTestimonials;
const currentIndex = ref(0);

const nextTestimonial = () => {
  currentIndex.value = (currentIndex.value + 1) % testimonials.length;
};

const prevTestimonial = () => {
  currentIndex.value = (currentIndex.value - 1 + testimonials.length) % testimonials.length;
};
</script>

<template>
  <section id="testimonials" class="relative w-full py-24 bg-gradient-to-b from-white to-gray-50">
    <div class="max-w-5xl mx-auto px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <div class="inline-block px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-full mb-4">
          قالوا عنا
        </div>
        <h2 class="text-4xl font-bold text-gray-900 mb-4">
          {{ section?.title || 'ماذا يقول عملاؤنا' }}
        </h2>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
          {{ section?.subtitle || 'آلاف رواد الأعمال وأصحاب المشاريع حققوا أهدافهم من خلال استشاراتنا' }}
        </p>
      </div>

      <!-- Testimonial Slider -->
      <div class="relative">
        <!-- Large Quote Mark Background -->
        <div class="absolute -top-16 left-8 text-[200px] text-gray-300 font-serif leading-none z-0 select-none opacity-40">
          "
        </div>

        <!-- Testimonial Card -->
        <div class="relative bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl shadow-2xl p-12 z-10 overflow-hidden">
          <div class="flex flex-col md:flex-row-reverse gap-8 items-stretch">
            <!-- Avatar on the right -->
            <div class="flex-shrink-0 flex items-center justify-center md:justify-end">
              <div v-if="testimonials[currentIndex].image" class="w-32 h-32 rounded-full overflow-hidden shadow-xl border-4 border-white">
                <img 
                  :src="`/storage/${testimonials[currentIndex].image}`" 
                  :alt="testimonials[currentIndex].name || testimonials[currentIndex].title || 'شهادة عميل'"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-32 h-32 bg-gradient-to-br from-[#205355] to-[#2B6F71] rounded-full flex items-center justify-center text-6xl shadow-xl">
                {{ testimonials[currentIndex].avatar || '👤' }}
              </div>
            </div>

            <!-- Content -->
            <div class="flex-1 flex flex-col justify-between text-right">
              <!-- Stars aligned to the right -->
              <div class="flex items-center justify-end gap-1 mb-6">
                <svg 
                  v-for="i in 5" 
                  :key="i" 
                  class="w-5 h-5 text-yellow-400" 
                  fill="currentColor" 
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>

              <!-- Testimonial Text -->
              <p class="text-2xl text-gray-800 leading-relaxed mb-6 font-medium">
                {{ testimonials[currentIndex].text || testimonials[currentIndex].description }}
              </p>

              <!-- Name & Role aligned to the right -->
              <div class="text-right">
                <div class="text-xl font-bold text-gray-900 mb-1">
                  {{ testimonials[currentIndex].name || testimonials[currentIndex].title }}
                </div>
                <div class="text-base text-gray-600">
                  {{ testimonials[currentIndex].role || testimonials[currentIndex].subtitle }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-center gap-4 mt-8">
          <button
            @click="prevTestimonial"
            class="w-12 h-12 bg-white border-2 border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 hover:border-[#205355] transition-all shadow-md"
          >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>

          <!-- Dots Indicator -->
          <div class="flex items-center gap-2">
            <button
              v-for="(_, index) in testimonials"
              :key="index"
              @click="currentIndex = index"
              class="w-2.5 h-2.5 rounded-full transition-all"
              :class="currentIndex === index ? 'bg-[#205355] w-8' : 'bg-gray-300'"
            ></button>
          </div>

          <button
            @click="nextTestimonial"
            class="w-12 h-12 bg-white border-2 border-gray-300 rounded-full flex items-center justify-center hover:bg-gray-50 hover:border-[#205355] transition-all shadow-md"
          >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
