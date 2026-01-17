<template>
  <div class="star-rating flex items-center gap-1">
    <!-- Stars Container -->
    <div class="stars-container flex items-center" :class="sizeClasses">
      <!-- Full Stars -->
      <svg
        v-for="index in fullStars"
        :key="`full-${index}`"
        class="star star-filled"
        :class="starSizeClass"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
      >
        <path
          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
        />
      </svg>

      <!-- Half Star -->
      <svg
        v-if="hasHalfStar"
        key="half"
        class="star star-half"
        :class="starSizeClass"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
      >
        <defs>
          <linearGradient :id="`half-fill-${uniqueId}`">
            <stop offset="50%" class="star-filled-stop" />
            <stop offset="50%" class="star-empty-stop" />
          </linearGradient>
        </defs>
        <path
          :fill="`url(#half-fill-${uniqueId})`"
          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
        />
      </svg>

      <!-- Empty Stars -->
      <svg
        v-for="index in emptyStars"
        :key="`empty-${index}`"
        class="star star-empty"
        :class="starSizeClass"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
      >
        <path
          d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
        />
      </svg>
    </div>

    <!-- Numeric Rating Display -->
    <span
      v-if="showNumeric"
      class="rating-numeric text-gray-700 dark:text-gray-300 font-medium"
      :class="numericSizeClass"
    >
      {{ formattedRating }}
    </span>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

/**
 * StarRating Component
 * 
 * Displays a rating using star icons with support for:
 * - Full stars, half stars, and empty stars
 * - Different sizes (sm, md, lg)
 * - Optional numeric display
 * - Dark mode support
 * - RTL/LTR support
 * 
 * Requirements: 8.1, 8.3, 8.4, 8.5, 8.6, 7.5
 */

interface StarRatingProps {
  rating: number; // 0-5
  readonly?: boolean;
  size?: 'sm' | 'md' | 'lg';
  showNumeric?: boolean;
}

const props = withDefaults(defineProps<StarRatingProps>(), {
  readonly: true,
  size: 'md',
  showNumeric: false,
});

// Generate unique ID for gradient to avoid conflicts when multiple components exist
const uniqueId = computed(() => Math.random().toString(36).substring(2, 9));

/**
 * Calculate the number of full stars
 * Requirements: 8.1, 8.3, 8.4
 */
const fullStars = computed(() => Math.floor(props.rating));

/**
 * Determine if a half star should be displayed
 * Requirements: 8.4
 */
const hasHalfStar = computed(() => {
  const decimal = props.rating % 1;
  return decimal >= 0.25 && decimal < 0.75;
});

/**
 * Calculate the number of empty stars
 * Requirements: 8.1, 8.5
 */
const emptyStars = computed(() => {
  const filled = fullStars.value + (hasHalfStar.value ? 1 : 0);
  return Math.max(0, 5 - filled);
});

/**
 * Format rating for display
 * Requirements: 8.6
 */
const formattedRating = computed(() => {
  return props.rating.toFixed(1);
});

/**
 * Size classes for the container
 */
const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'gap-0.5';
    case 'lg':
      return 'gap-1.5';
    default:
      return 'gap-1';
  }
});

/**
 * Size classes for individual stars
 */
const starSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-4 h-4';
    case 'lg':
      return 'w-7 h-7';
    default:
      return 'w-5 h-5';
  }
});

/**
 * Size classes for numeric display
 */
const numericSizeClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'text-sm';
    case 'lg':
      return 'text-lg';
    default:
      return 'text-base';
  }
});
</script>

<style scoped>
/* Star styling - using plain CSS */

/* Filled stars - yellow color */
.star-filled {
  color: rgb(250 204 21); /* yellow-400 */
}

:global(.dark) .star-filled {
  color: rgb(253 224 71); /* yellow-300 */
}

/* Empty stars - gray color */
.star-empty {
  color: rgb(209 213 219); /* gray-300 */
}

:global(.dark) .star-empty {
  color: rgb(75 85 99); /* gray-600 */
}

/* Gradient stops for half star */
.star-filled-stop {
  stop-color: rgb(250 204 21); /* yellow-400 */
}

:global(.dark) .star-filled-stop {
  stop-color: rgb(253 224 71); /* yellow-300 */
}

.star-empty-stop {
  stop-color: rgb(209 213 219); /* gray-300 */
}

:global(.dark) .star-empty-stop {
  stop-color: rgb(75 85 99); /* gray-600 */
}

/* Smooth transitions */
.star {
  transition: color 0.2s ease;
}

/* Ensure stars are aligned properly */
.stars-container {
  flex-shrink: 0;
}
</style>
