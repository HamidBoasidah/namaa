<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="relative w-full bg-[#2B6F71] text-white">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-8 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
        <!-- Company Info -->
        <div class="space-y-6">
          <div>
            <h3 class="text-2xl font-bold mb-2">نماء الأعمال</h3>
            <p class="text-gray-300 leading-relaxed">
              منصة تربط رواد الأعمال وأصحاب المشاريع بمستشارين ماليين ومحاسبين معتمدين لتقديم استشارات احترافية وخدمات متخصصة.
            </p>
          </div>

          <!-- Contact Info -->
          <div class="space-y-3">
            <a 
              href="mailto:info@consultant-platform.com" 
              class="flex items-center gap-3 text-gray-300 hover:text-white transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <span>info@consultant-platform.com</span>
            </a>

            <a 
              href="tel:+966501234567" 
              class="flex items-center gap-3 text-gray-300 hover:text-white transition-colors"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              <span>+966 50 123 4567</span>
            </a>

            <div class="flex items-start gap-3 text-gray-300">
              <svg class="w-5 h-5 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>الرياض، المملكة العربية السعودية</span>
            </div>
          </div>
        </div>

        <!-- Services Links -->
        <div>
          <h4 class="text-lg font-bold mb-6">الخدمات</h4>
          <ul class="space-y-3">
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                للعملاء
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                للمستشارين
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                الأسعار
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                الخدمات المتاحة
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                شركاء النجاح
              </Link>
            </li>
          </ul>
        </div>

        <!-- Company Links -->
        <div>
          <h4 class="text-lg font-bold mb-6">الشركة</h4>
          <ul class="space-y-3">
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                من نحن
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                كيف نعمل
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                المميزات
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                الخدمات
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                اتصل بنا
              </Link>
            </li>
          </ul>
        </div>

        <!-- Support Links -->
        <div>
          <h4 class="text-lg font-bold mb-6">الدعم</h4>
          <ul class="space-y-3">
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                مركز المساعدة
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                الأسئلة الشائعة
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                سياسة الاسترداد
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                الشروط والأحكام
              </Link>
            </li>
            <li>
              <Link href="#" class="text-gray-300 hover:text-white transition-colors">
                سياسة الخصوصية
              </Link>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-white/20">
      <div class="max-w-7xl mx-auto px-8 py-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
          <!-- Copyright -->
          <p class="text-gray-300 text-sm">
            © {{ currentYear }} نماء الأعمال. جميع الحقوق محفوظة.
          </p>

          <!-- Social Links -->
          <div class="flex items-center gap-4">
            <a 
              href="#" 
              class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
              aria-label="LinkedIn"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
              </svg>
            </a>

            <a 
              href="#" 
              class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
              aria-label="Instagram"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
              </svg>
            </a>

            <a 
              href="#" 
              class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
              aria-label="Facebook"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </a>

            <a 
              href="#" 
              class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-white/20 transition-colors"
              aria-label="Twitter/X"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>
