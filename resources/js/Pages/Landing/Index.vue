<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingLayout from '@/Layouts/LandingLayout.vue';
import HeroSection from '@/components/Landing/HeroSection.vue';
import FeaturesSection from '@/components/Landing/FeaturesSection.vue';
import HowItWorksSection from '@/components/Landing/HowItWorksSection.vue';
import ServicesSection from '@/components/Landing/ServicesSection.vue';
import MobileAppSection from '@/components/Landing/MobileAppSection.vue';
import TestimonialsSection from '@/components/Landing/TestimonialsSection.vue';
import FaqSection from '@/components/Landing/FaqSection.vue';
import FooterSection from '@/components/Landing/FooterSection.vue';

interface Section {
  id: number;
  type: string;
  title?: string;
  subtitle?: string;
  description?: string;
  content?: any;
  settings?: any;
  background_color?: string;
  image?: string;
  items?: any[];
}

interface Props {
  page: {
    id: number;
    title: string;
    slug: string;
    meta_title?: string;
    meta_description?: string;
  };
  sections: Section[];
}

const props = defineProps<Props>();

const getSectionComponent = (type: string) => {
  const components: Record<string, any> = {
    hero: HeroSection,
    features: FeaturesSection,
    how_it_works: HowItWorksSection,
    services: ServicesSection,
    mobile_app: MobileAppSection,
    testimonials: TestimonialsSection,
    faq: FaqSection,
  };
  return components[type];
};
</script>

<template>
  <LandingLayout>
    <Head>
      <title>{{ page.meta_title || page.title }}</title>
      <meta 
        v-if="page.meta_description" 
        name="description" 
        :content="page.meta_description" 
      />
    </Head>

    <!-- Render sections dynamically -->
    <component
      v-for="section in sections"
      :key="section.id"
      :is="getSectionComponent(section.type)"
      :section="section"
    />

    <!-- Footer -->
    <FooterSection />
  </LandingLayout>
</template>
