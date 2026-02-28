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

const props = defineProps<Props>();
</script>

<template>
  <section class="relative w-full h-[798px] overflow-hidden">
    <!-- Background Gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-[#ECFDF5]/60 to-[#D1FAE5]/60"></div>

    <!-- Blurred Background Shapes -->
    <div 
      class="absolute w-[1055px] h-[981px] -right-[508px] top-[293px] bg-[#205355]/30 rounded-[100px] blur-[142px] transform -rotate-[10deg]"
    ></div>
    <div 
      class="absolute w-[479px] h-[446px] left-[1099px] -top-[49px] bg-[#205355]/30 rounded-[100px] blur-[142px] transform -rotate-[10deg]"
    ></div>

    <!-- Content Container -->
    <div class="relative z-10 max-w-[1200px] mx-auto pt-[122px] flex flex-col items-center gap-8 px-4">
      <!-- Text Content -->
      <div class="flex flex-col items-center gap-6">
        <!-- Main Title -->
        <h1 class="w-full max-w-[835px] text-center text-5xl font-bold leading-[150%] tracking-[-0.02em] text-[#1A4344]">
          {{ section.title || 'خُذ قراراتك بثقة مع خبراء مال وأعمال بجانبك دائمًا' }}
        </h1>

        <!-- Subtitle -->
        <p class="w-full max-w-[791px] text-center text-xl leading-8 text-[#1F242E]">
          {{ section.subtitle || 'من تحليل القوائم المالية إلى دراسة الجدوى وخطط النمو، كل ما تحتاجه متاح في مكان واحد.' }}
        </p>
      </div>

      <!-- Video Call Frame with Side Elements -->
      <div class="relative w-full flex items-start justify-center gap-8">
        <!-- Rating Badge with Testimonial - Left Side -->
        <div class="flex flex-col items-center gap-6 flex-shrink-0">
          <!-- Rating Badge Circle -->
          <div class="flex flex-col items-center justify-center gap-2 w-[180px] h-[180px] bg-white rounded-full shadow-xl border border-gray-100">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M24 4L29.18 18.26L44 20.27L33.46 30.14L36.36 44.73L24 37.27L11.64 44.73L14.54 30.14L4 20.27L18.82 18.26L24 4Z" fill="#FFC107"/>
            </svg>
            <div class="text-5xl font-bold text-gray-900">4.9</div>
          </div>

          <!-- Testimonial Card -->
          <div class="flex flex-col gap-3 w-[300px] bg-white rounded-2xl shadow-xl border border-gray-100 p-5">
            <p class="text-sm text-gray-700 leading-relaxed text-center">
              "جلسة الاستشارة التجارية وضحت<br>لي نقاط ضعف المنافسين<br>وزودتني بأفكار جديدة للتطوير."
            </p>
            <Link 
              href="#testimonials" 
              class="text-sm font-medium text-[#205355] hover:text-[#2B6F71] text-center"
            >
              اقرأ المزيد
            </Link>
          </div>
        </div>

        <!-- Video Call Frame Center -->
        <div class="relative w-[485px] h-[391px] flex-shrink-0">
          <!-- Frame Border -->
          <div class="absolute w-[484.9px] h-[375px] left-0 top-4 border-[5.75px] border-[#2B6F71] rounded-[27.6px]"></div>

          <!-- Video Image -->
          <div class="absolute w-[484.71px] h-[397px] left-0 -top-[25px] rounded-[27.6px] overflow-hidden">
            <img 
              v-if="section.image"
              :src="`/storage/${section.image}`" 
              alt="مستشار مالي"
              class="w-full h-full object-cover"
            />
            <img 
              v-else
              src="/images/landing/hero-consultant.jpg" 
              alt="مستشار مالي"
              class="w-full h-full object-cover"
            />
          </div>

            <!-- Control Icons at Bottom Inside Frame -->
          <div class="absolute left-1/2 transform -translate-x-1/2 bottom-[40px] flex items-center justify-center gap-4 z-20">
            <!-- Video Icon -->
            <div class="flex items-center justify-center w-[50px] h-[50px] bg-white/90 backdrop-blur-sm rounded-full cursor-pointer hover:bg-white transition-colors shadow-md">
              <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M18.6667 13.0667L26.2534 8.52004C27.36 7.85337 28.6667 8.65337 28.6667 9.94671V22.0534C28.6667 23.3467 27.36 24.1467 26.2534 23.48L18.6667 18.9334V13.0667Z" fill="#205355"/>
                <path d="M15.9999 24H7.99992C5.33325 24 3.33325 22.6667 3.33325 19.3333V12.6667C3.33325 9.33337 5.33325 8.00004 7.99992 8.00004H15.9999C18.6666 8.00004 20.6666 9.33337 20.6666 12.6667V19.3333C20.6666 22.6667 18.6666 24 15.9999 24Z" fill="#22595A"/>
              </svg>
            </div>

            <!-- Microphone Icon -->
            <div class="flex items-center justify-center w-[50px] h-[50px] bg-white/90 backdrop-blur-sm rounded-full cursor-pointer hover:bg-white transition-colors shadow-md">
              <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 5.66675C13.4267 5.66675 11.3333 7.76008 11.3333 10.3334V16.0001C11.3333 18.5734 13.4267 20.6667 16 20.6667C18.5733 20.6667 20.6667 18.5734 20.6667 16.0001V10.3334C20.6667 7.76008 18.5733 5.66675 16 5.66675Z" fill="#22595A"/>
                <path d="M23.3333 13.6667V16.0001C23.3333 20.4267 19.76 24.0001 15.3333 24.0001H16.6667C21.0933 24.0001 24.6667 20.4267 24.6667 16.0001V13.6667H23.3333Z" fill="#205355"/>
              </svg>
            </div>

            <!-- Chat Icon -->
            <div class="flex items-center justify-center w-[50px] h-[50px] bg-white/90 backdrop-blur-sm rounded-full cursor-pointer hover:bg-white transition-colors shadow-md">
              <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22.6667 28H9.33333C4 28 2.66667 26.6667 2.66667 21.3333V10.6667C2.66667 5.33333 4 4 9.33333 4H22.6667C28 4 29.3333 5.33333 29.3333 10.6667V21.3333C29.3333 26.6667 28 28 22.6667 28Z" fill="#22595A"/>
                <path d="M22.6668 12H9.3335" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
                <path d="M17.3335 20H9.3335" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
              </svg>
            </div>

            <!-- End Call Icon -->
            <div class="flex items-center justify-center w-[50px] h-[50px] bg-red-500 rounded-full cursor-pointer hover:bg-red-600 transition-colors shadow-md">
              <svg width="24" height="24" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 11L22 23M22 11L10 23" stroke="white" stroke-width="2.5" stroke-linecap="round"/>
              </svg>
            </div>
          </div>
        </div>

        <!-- Consultants Badge - Right Side -->
        <div class="flex flex-col items-center justify-center w-[180px] h-[180px] bg-white rounded-full shadow-xl border border-gray-100 p-4 flex-shrink-0">
          <div class="flex items-center justify-center gap-1 mb-3">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FF878A] to-[#FF6B6B] border-2 border-white z-10"></div>
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#4ECDC4] to-[#44A5A5] border-2 border-white -mr-4"></div>
          </div>
          <div class="text-center">
            <div class="text-5xl font-bold text-gray-900 mb-1">+50</div>
            <div class="text-xs text-gray-600 leading-tight">مستشار مالي<br>وتجاري</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
