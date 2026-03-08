<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

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
  { title: 'محمد السعيد', subtitle: 'مشغل أونجة البدنيل', rating: 5, description: '"المستشار كان محترفاً جداً وقدم لي نصائح عملية لتحسين التدفقات النقدية."', avatar: '👨‍💼' },
  { title: 'فاطمة أحمد', subtitle: 'صاحبة مشروع تجاري', rating: 5, description: '"جلسة الاستشارة وضحت لي نقاط ضعف المنافسين وزودتني بأفكار جديدة للتطوير."', avatar: '👩‍💼' },
  { title: 'خالد العتيبي', subtitle: 'رائد أعمال', rating: 5, description: '"حصلت على دراسة جدوى كاملة في وقت قياسي، ساعدتني في إقناع المستثمرين."', avatar: '👨‍💻' },
];

const testimonials = props.section?.items?.length ? props.section.items : defaultTestimonials;
const currentIndex = ref(0);
const AUTOPLAY_MS = 5000;
let autoplayTimer: ReturnType<typeof setInterval> | null = null;

function goTo(index: number) {
  currentIndex.value = index;
}

function next() {
  currentIndex.value = (currentIndex.value + 1) % testimonials.length;
}

function startAutoplay() {
  stopAutoplay();
  autoplayTimer = setInterval(next, AUTOPLAY_MS);
}

function stopAutoplay() {
  if (autoplayTimer) {
    clearInterval(autoplayTimer);
    autoplayTimer = null;
  }
}

onMounted(() => {
  startAutoplay();
});
onUnmounted(() => {
  stopAutoplay();
});
</script>

<template>
  <section id="testimonials" class="relative py-20 lg:py-28 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-14">
        <h2 class="text-3xl sm:text-4xl font-bold text-stone-900 mb-3">
          آراء العملاء
        </h2>
        <p class="text-lg text-stone-600">
          {{ section?.title || 'انطباعات حقيقية من عملائنا' }}
        </p>
        <p class="text-stone-500 mt-1">
          {{ section?.subtitle || 'رواد أعمال وأصحاب مشاريع حققوا أهدافهم من خلال استشاراتنا' }}
        </p>
      </div>

      <div class="relative">
        <div class="absolute top-0 right-4 text-[100px] text-stone-100 font-serif leading-none select-none">
          "
        </div>

        <div class="relative bg-stone-50 rounded-2xl border border-stone-200 p-8 sm:p-10">
          <div class="flex flex-col md:flex-row-reverse gap-8 items-center">
            <div class="shrink-0">
              <div
                v-if="testimonials[currentIndex].image"
                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl overflow-hidden border-2 border-white shadow-md"
              >
                <img
                  :src="`/storage/${testimonials[currentIndex].image}`"
                  :alt="testimonials[currentIndex].title || ''"
                  class="w-full h-full object-cover"
                />
              </div>
              <div
                v-else
                class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl flex items-center justify-center text-3xl shadow-md"
                style="background-color: #028187; color: white;"
              >
                {{ testimonials[currentIndex].avatar || '👤' }}
              </div>
            </div>

            <div class="flex-1 text-right">
              <div class="flex justify-end gap-0.5 mb-3">
                <svg
                  v-for="i in 5"
                  :key="i"
                  class="w-5 h-5 text-amber-400"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <p class="text-lg sm:text-xl text-stone-800 leading-relaxed mb-5">
                {{ testimonials[currentIndex].text || testimonials[currentIndex].description }}
              </p>
              <div>
                <div class="font-bold text-stone-900">
                  {{ testimonials[currentIndex].name || testimonials[currentIndex].title }}
                </div>
                <div class="text-stone-500 text-sm">
                  {{ testimonials[currentIndex].role || testimonials[currentIndex].subtitle }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Dots only - no arrows; clickable; auto-play -->
        <div class="flex items-center justify-center gap-2 mt-6">
          <button
            v-for="(_, index) in testimonials"
            :key="index"
            type="button"
            @click="goTo(index)"
            class="rounded-full transition-all duration-300 h-2.5 focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#028187]"
            :class="currentIndex === index ? 'w-8 bg-[#028187]' : 'w-2.5 bg-stone-300 hover:bg-stone-400'"
            :aria-label="`الشهادة ${index + 1}`"
          />
        </div>
      </div>
    </div>
  </section>
</template>
