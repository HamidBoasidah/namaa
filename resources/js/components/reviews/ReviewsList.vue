<template>
  <div class="reviews-list">
    <!-- Loading State -->
    <div v-if="loading" class="loading-state flex items-center justify-center py-12">
      <div class="text-center">
        <SpinnerThree />
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
          {{ t('common.loading') }}
        </p>
      </div>
    </div>

    <!-- Empty State -->
    <div 
      v-else-if="reviews.length === 0" 
      class="empty-state min-h-[300px] bg-white dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-700 flex flex-col items-center justify-center py-12 px-4 transition-colors duration-200"
    >
      <div class="empty-icon mb-4">
        <svg
          class="w-16 h-16 text-gray-400 dark:text-gray-600"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
        {{ t('reviews.noReviews') }}
      </h3>
      <p class="text-sm text-gray-600 dark:text-gray-400">
        {{ t('reviews.noReviewsDescription') }}
      </p>
    </div>

    <!-- Reviews Grid -->
    <div 
      v-else 
      class="reviews-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6"
    >
      <ReviewItem 
        v-for="review in reviews" 
        :key="review.id" 
        :review="review"
        class="review-item-wrapper"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import ReviewItem from './ReviewItem.vue';
import SpinnerThree from '@/components/spinner/SpinnerThree.vue';
import type { Review } from '@/types/reviews';

/**
 * ReviewsList Component
 * 
 * Displays a list of reviews with:
 * - Loading state with spinner
 * - Empty state when no reviews exist
 * - Responsive grid layout for reviews
 * 
 * Features:
 * - Responsive design (1 column on mobile, 2 on tablet, 3 on desktop)
 * - Dark mode support
 * - RTL/LTR support
 * - Loading indicator
 * - Empty state message
 * 
 * Requirements: 1.3, 1.4, 9.1, 9.2, 5.1, 5.2, 5.3
 */

interface ReviewsListProps {
  reviews: Review[];
  loading: boolean;
}

const props = defineProps<ReviewsListProps>();

const { t } = useI18n();
</script>

<style scoped>
/* ReviewsList styling - using plain CSS */

.reviews-list {
  width: 100%;
}

.loading-state {
  min-height: 300px;
}

.empty-icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Animation for review items */
.review-item-wrapper {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Stagger animation for multiple items */
.review-item-wrapper:nth-child(1) { animation-delay: 0s; }
.review-item-wrapper:nth-child(2) { animation-delay: 0.05s; }
.review-item-wrapper:nth-child(3) { animation-delay: 0.1s; }
.review-item-wrapper:nth-child(4) { animation-delay: 0.15s; }
.review-item-wrapper:nth-child(5) { animation-delay: 0.2s; }
.review-item-wrapper:nth-child(6) { animation-delay: 0.25s; }
</style>
