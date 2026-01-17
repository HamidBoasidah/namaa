<template>
  <div 
    class="review-item bg-white dark:bg-gray-800 rounded-lg p-4 md:p-6 shadow-sm border border-gray-200 dark:border-gray-700 transition-colors duration-200"
  >
    <!-- Review Header -->
    <div class="review-header flex items-start gap-3 md:gap-4 mb-4">
      <!-- Client Avatar -->
      <ClientAvatar 
        :src="review.client_avatar" 
        :name="review.client_name" 
        :size="avatarSize"
      />
      
      <!-- Review Meta Information -->
      <div class="review-meta flex-1 min-w-0">
        <!-- Client Name -->
        <h4 class="client-name text-base md:text-lg font-semibold text-gray-900 dark:text-white mb-1 truncate">
          {{ review.client_name }}
        </h4>
        
        <!-- Rating -->
        <div class="mb-2">
          <StarRating 
            :rating="review.rating" 
            :readonly="true" 
            size="sm" 
            :showNumeric="false"
          />
        </div>
        
        <!-- Review Date -->
        <time 
          class="review-date text-xs md:text-sm text-gray-500 dark:text-gray-400"
          :datetime="review.created_at"
        >
          {{ formattedDate }}
        </time>
      </div>
    </div>
    
    <!-- Review Body -->
    <div class="review-body">
      <p class="review-comment text-sm md:text-base text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
        {{ review.comment }}
      </p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import 'dayjs/locale/ar';
import 'dayjs/locale/en';
import ClientAvatar from './ClientAvatar.vue';
import StarRating from './StarRating.vue';
import type { Review } from '@/types/reviews';

/**
 * ReviewItem Component
 * 
 * Displays a single review with:
 * - Client avatar and name
 * - Star rating
 * - Review date (formatted according to locale)
 * - Review comment
 * 
 * Features:
 * - RTL/LTR support
 * - Dark mode support
 * - Responsive design
 * - Localized date formatting
 * 
 * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 6.6
 */

// Extend dayjs with plugins
dayjs.extend(relativeTime);
dayjs.extend(localizedFormat);

interface ReviewItemProps {
  review: Review;
  avatarSize?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<ReviewItemProps>(), {
  avatarSize: 'md',
});

const { locale } = useI18n();

/**
 * Format the review date according to the current locale
 * Requirements: 6.6
 * 
 * For Arabic: uses Arabic locale
 * For English: uses English locale
 * 
 * Shows relative time for recent reviews (e.g., "2 days ago")
 * Shows formatted date for older reviews (e.g., "Jan 15, 2024")
 */
const formattedDate = computed(() => {
  if (!props.review.created_at) return '';
  
  // Set dayjs locale based on current i18n locale
  const currentLocale = locale.value === 'ar' ? 'ar' : 'en';
  dayjs.locale(currentLocale);
  
  const reviewDate = dayjs(props.review.created_at);
  const now = dayjs();
  const daysDiff = now.diff(reviewDate, 'day');
  
  // Show relative time for reviews within the last 7 days
  if (daysDiff < 7) {
    return reviewDate.fromNow();
  }
  
  // Show formatted date for older reviews
  // Format: "Jan 15, 2024" (English) or "١٥ يناير ٢٠٢٤" (Arabic)
  return reviewDate.format('LL');
});
</script>

<style scoped>
/* ReviewItem styling - using plain CSS */

.review-item {
  transition: all 0.2s ease;
}

.review-item:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.review-meta {
  overflow: hidden;
}

.client-name {
  word-wrap: break-word;
}

.review-comment {
  word-wrap: break-word;
}

@media (max-width: 768px) {
  .review-item {
    padding: 1rem;
  }
  
  .review-header {
    gap: 0.75rem;
  }
}
</style>
