<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="bg-brand-500 text-white">
    <div id="contact" class="border-b border-white/20">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center max-w-2xl mx-auto mb-8">
          <h3 class="text-2xl font-bold text-white mb-3">
            {{ t('landing.footer.tagline') }}
          </h3>
          <p class="text-white/90 text-sm leading-relaxed mb-6">
            {{ t('landing.footer.description') }}
          </p>
          <div class="flex flex-wrap items-center justify-center gap-4">
            <Link
              href="/register"
              class="footer-cta-primary inline-flex items-center gap-2 px-6 py-3 font-semibold rounded-xl transition-all hover:shadow-lg"
            >
              {{ t('landing.footer.learnMore') }}
            </Link>
            <Link
              href="#services"
              class="inline-flex items-center gap-2 px-6 py-3 border-2 border-white/40 text-white font-semibold rounded-xl hover:bg-white/10 hover:border-white/60 transition-all"
            >
              {{ t('landing.footer.exploreServices') }}
            </Link>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
        <!-- Brand -->
        <div class="lg:col-span-1 space-y-4">
          <Link href="/" class="inline-flex items-center gap-2">
            <img src="/images/logo/logo-euro.png" alt="كسب" class="h-10 w-auto footer-logo" />
          </Link>
          <p class="text-white/80 leading-relaxed text-sm">
            {{ t('landing.footer.platformDescription') }}
          </p>
          <div class="flex flex-col gap-2 text-sm">
            <a href="mailto:info@consultant-platform.com" class="flex items-center gap-2 hover:text-white transition-colors">
              <span class="text-white/90">info@consultant-platform.com</span>
            </a>
            <a href="tel:+966501234567" class="flex items-center gap-2 hover:text-white transition-colors">
              <span class="text-white/90">+966 50 123 4567</span>
            </a>
            <span class="flex items-start gap-2 text-white/70">
              {{ t('landing.footer.address') }}
            </span>
          </div>
        </div>

        <div>
          <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">{{ t('landing.footer.importantLinks') }}</h4>
          <ul class="space-y-3 text-sm">
            <li><Link href="/" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.home') }}</Link></li>
            <li><Link href="#features" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.features') }}</Link></li>
            <li><Link href="#services" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.ourServices') }}</Link></li>
            <li><Link href="#faq" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.faq') }}</Link></li>
            <li><Link href="#contact" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.contactUs') }}</Link></li>
          </ul>
        </div>

        <div>
          <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">{{ t('landing.footer.services') }}</h4>
          <ul class="space-y-3 text-sm">
            <li><Link href="#" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.forClients') }}</Link></li>
            <li><Link href="#" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.forAdvisors') }}</Link></li>
            <li><Link href="#" class="text-white/80 hover:text-white transition-colors">{{ t('landing.footer.pricing') }}</Link></li>
          </ul>
        </div>

        <div>
          <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">{{ t('landing.footer.socialMedia') }}</h4>
          <div class="flex items-center gap-3">
            <a href="#" class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-white/90 hover:bg-white hover:text-brand-500 transition-colors" aria-label="LinkedIn">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
            </a>
            <a href="#" class="w-10 h-10 rounded-lg bg-white/10 flex items-center justify-center text-white/90 hover:bg-white hover:text-brand-500 transition-colors" aria-label="Twitter">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
          </div>
        </div>
      </div>

      <div class="mt-14 pt-8 border-t border-white/20 flex flex-col sm:flex-row items-center justify-center gap-4">
        <p class="text-sm text-white/70 text-center">
          © {{ currentYear }} كسب. {{ t('landing.footer.copyright') }}
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.footer-logo {
  mix-blend-mode: screen;
}
.footer-cta-primary {
  background-color: var(--color-white);
  color: var(--color-brand-500);
}
.footer-cta-primary:hover {
  background-color: #f3f4f6;
}
</style>
