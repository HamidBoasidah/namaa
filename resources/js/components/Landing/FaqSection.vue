<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  section?: {
    title?: string;
    subtitle?: string;
    items?: Array<{
      title: string;
      description: string;
    }>;
  };
}

const props = defineProps<Props>();

const defaultFaqs = [
  {
    question: 'كيف يمكنني حجز استشارة؟',
    answer: 'يمكنك تصفح المستشارين المتاحين، اختيار المستشار المناسب، ثم تحديد نوع الاستشارة (فيديو/صوت/نص) والوقت المناسب لك. بعد إتمام الدفع الآمن، ستتلقى تأكيداً بالحجز وتفاصيل الاتصال.',
    isOpen: true,
  },
  {
    question: 'ما أنواع الخدمات المتاحة؟',
    answer: 'نوفر استشارات مالية ومحاسبية متنوعة، بالإضافة إلى خدمات مثل دراسة الجدوى، تحليل السوق، التخطيط المالي، إعداد القوائم المالية، الاستشارات الضريبية، وتقييم المشاريع.',
    isOpen: false,
  },
  {
    question: 'كيف يتم فحص المستشارين؟',
    answer: 'جميع المستشارين على منصتنا يخضعون لعملية تحقق صارمة تشمل التحقق من المؤهلات الأكاديمية والشهادات المهنية والخبرة العملية قبل الموافقة على انضمامهم للمنصة.',
    isOpen: false,
  },
  {
    question: 'كيف يتلقى المستشارون أرباحهم؟',
    answer: 'يتلقى المستشارون أرباحهم بشكل دوري (أسبوعياً أو شهرياً) من خلال تحويل بنكي مباشر أو عبر المحفظة الإلكترونية المتاحة في التطبيق.',
    isOpen: false,
  },
  {
    question: 'هل الاستشارات سرية وآمنة؟',
    answer: 'نعم، جميع الاستشارات والبيانات محمية بأحدث تقنيات التشفير. نلتزم بسياسات خصوصية صارمة لضمان حماية معلوماتك وبياناتك المالية.',
    isOpen: false,
  },
  {
    question: 'ماذا أفعل إذا واجهت مشكلة تقنية أثناء الاستشارة؟',
    answer: 'يمكنك التواصل مع فريق الدعم الفني على مدار الساعة من خلال الشات المباشر في التطبيق أو عبر البريد الإلكتروني. سنقوم بحل أي مشكلة فوراً وإعادة جدولة الجلسة إذا لزم الأمر.',
    isOpen: false,
  },
];

const faqs = ref(props.section?.items?.map((item, index) => ({
  question: item.title,
  answer: item.description,
  isOpen: index === 0,
})) || defaultFaqs);

const toggleFaq = (index: number) => {
  faqs.value[index].isOpen = !faqs.value[index].isOpen;
};
</script>

<template>
  <section id="faq" class="relative w-full py-24 bg-white">
    <div class="max-w-7xl mx-auto px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-start">
        <!-- Left Side: Image/Illustration -->
        <div class="relative flex items-center justify-center">
          <div class="relative">
            <!-- Question Marks Illustration -->
            <div class="text-[200px] text-gray-200 font-bold leading-none select-none">
              ؟
            </div>
            
            <!-- Floating Question Marks -->
            <div class="absolute top-0 right-0 text-6xl text-[#205355]/20 animate-bounce" style="animation-delay: 0s">
              ؟
            </div>
            <div class="absolute top-20 -right-12 text-5xl text-[#2B6F71]/20 animate-bounce" style="animation-delay: 0.2s">
              ؟
            </div>
            <div class="absolute -bottom-10 right-12 text-7xl text-[#205355]/20 animate-bounce" style="animation-delay: 0.4s">
              ؟
            </div>
            
            <!-- Hand Image -->
            <div class="absolute -bottom-20 -left-20 w-64 h-64">
              <div class="text-9xl">
                🤔
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side: FAQs -->
      <div>

          <!-- FAQ Accordion -->
          <div class="space-y-4">
            <div
              v-for="(faq, index) in faqs"
              :key="index"
              class="border border-gray-200 rounded-2xl overflow-hidden transition-all hover:border-[#205355]"
              :class="faq.isOpen ? 'bg-[#EAF1F1]' : 'bg-white'"
            >
              <!-- Question Button -->
              <button
                @click="toggleFaq(index)"
                class="w-full px-6 py-5 flex items-center justify-between text-right transition-colors"
              >
                <span class="text-lg font-bold text-gray-900 flex-1 pr-4">
                  {{ faq.question }}
                </span>
                <svg
                  class="w-6 h-6 text-gray-600 transform transition-transform flex-shrink-0"
                  :class="faq.isOpen ? 'rotate-180' : ''"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <!-- Answer -->
              <div
                v-show="faq.isOpen"
                class="px-6 pb-5 text-gray-700 leading-relaxed"
              >
                {{ faq.answer }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

.animate-bounce {
  animation: bounce 3s ease-in-out infinite;
}
</style>
