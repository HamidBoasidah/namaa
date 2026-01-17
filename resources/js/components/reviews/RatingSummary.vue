<template>
  <div 
    class="rating-summary bg-white dark:bg-gray-800 rounded-lg p-4 md:p-6 shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-200"
  >
    <!-- Average Rating Display -->
    <div class="average-rating flex flex-col md:flex-row items-center md:items-start gap-4 mb-3">
      <!-- Rating Number -->
      <div class="rating-number-container text-center md:text-left">
        <div class="rating-number text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
          {{ formattedRating }}
        </div>
        <div class="rating-label text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ t('reviews.averageRating') }}
        </div>
      </div>
      
      <!-- Star Rating -->
      <div class="star-rating-container flex flex-col items-center md:items-start justify-center">
        <StarRating 
          :rating="averageRating" 
          :readonly="true" 
          size="lg"
          :showNumeric="false"
        />
        
        <!-- Total Reviews Count -->
        <div class="total-reviews mt-2 text-sm text-gray-600 dark:text-gray-400">
          {{ t('reviews.total', totalReviews) }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import StarRating from './StarRating.vue';

/**
 * RatingSummary Component
 * 
 * Displays a summary of ratings including:
 * - Average rating (numeric)
 * - Star rating visualization
 * - Total number of reviews
 * 
 * Features:
 * - RTL/LTR support
 * - Dark mode support
 * - Responsive design
 * - i18n with plural forms support
 * 
 * Requirements: 3.1, 3.2, 3.3
 */

interface RatingSummaryProps {
  averageRating: number;
  totalReviews: number;
}

const props = defineProps<RatingSummaryProps>();

const { t } = useI18n();

/**
 * Format the average rating to one decimal place
 * Requirements: 3.1
 */
const formattedRating = computed(() => {
  return props.averageRating.toFixed(1);
});
</script>

<style scoped>
/* RatingSummary styling - using plain CSS */

.rating-summary {
  transition: all 0.2s ease;
}

.rating-summary:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.rating-number {
  font-weight: bold;
  line-height: 1.2;
}

.total-reviews {
  font-weight: 500;
}

@media (max-width: 768px) {
  .rating-summary {
    text-align: center;
  }
  
  .average-rating {
    flex-direction: column;
    align-items: center;
  }
  
  .rating-number-container {
    text-align: center;
  }
}
</style>
