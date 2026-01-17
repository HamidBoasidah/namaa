<template>
  <div class="reviews-section space-y-6">
    <!-- Rating Summary -->
    <RatingSummary 
      :averageRating="averageRating" 
      :totalReviews="totalReviews"
    />

    <!-- Error State -->
    <div 
      v-if="error" 
      class="error-state bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6 text-center"
    >
      <div class="error-icon mb-4 flex justify-center">
        <svg
          class="w-12 h-12 text-red-500 dark:text-red-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <p class="error-message text-red-700 dark:text-red-300 mb-4">
        {{ error }}
      </p>
      <button
        @click="retryFetch"
        class="retry-button px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200"
      >
        {{ t('reviews.retry') }}
      </button>
    </div>

    <!-- Reviews List -->
    <ReviewsList 
      v-else
      :reviews="reviews" 
      :loading="loading"
    />

    <!-- Pagination -->
    <ReviewsPagination
      v-if="showPagination"
      :currentPage="pagination.current_page"
      :totalPages="pagination.last_page"
      :perPage="pagination.per_page"
      :total="pagination.total"
      @page-change="handlePageChange"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import RatingSummary from './RatingSummary.vue';
import ReviewsList from './ReviewsList.vue';
import ReviewsPagination from './ReviewsPagination.vue';
import type { 
  Review, 
  PaginationMeta,
  PaginatedReviews
} from '@/types/reviews';

/**
 * ReviewsSection Component
 * 
 * Main container component that manages reviews state and integrates all child components.
 */

interface Props {
  entityType: 'consultant' | 'service';
  entityId: number;
  initialReviews?: PaginatedReviews;
}

const props = withDefaults(defineProps<Props>(), {
  initialReviews: undefined,
});

const { t } = useI18n();

// Reactive state
const reviews = ref<Review[]>([]);
const pagination = ref<PaginationMeta>({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0,
  from: 0,
  to: 0,
});
const loading = ref(false);
const error = ref<string | null>(null);
const averageRating = ref(0);
const totalReviews = ref(0);

/**
 * Determine if pagination should be shown
 */
const showPagination = computed(() => {
  return pagination.value.total > pagination.value.per_page;
});

/**
 * Calculate average rating from reviews
 */
const calculateAverageRating = (): number => {
  if (reviews.value.length === 0) return 0;
  
  const sum = reviews.value.reduce((acc, review) => acc + review.rating, 0);
  return sum / reviews.value.length;
};

/**
 * Fetch reviews from API using fetch()
 */
const fetchReviews = async (page: number = 1) => {
  // Skip if no valid entityId
  if (!props.entityId || props.entityId <= 0) {
    return;
  }

  try {
    loading.value = true;
    error.value = null;

    // Construct API endpoint based on entity type
    const endpoint = props.entityType === 'consultant'
      ? `/api/consultants/${props.entityId}/reviews?page=${page}`
      : `/api/consultant-services/${props.entityId}/reviews?page=${page}`;

    const response = await fetch(endpoint, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result = await response.json();

    if (result.success && result.data) {
      reviews.value = result.data;
      
      // Handle pagination from API response
      if (result.pagination) {
        pagination.value = {
          current_page: result.pagination.current_page,
          last_page: result.pagination.last_page,
          per_page: result.pagination.per_page,
          total: result.pagination.total,
          from: (result.pagination.current_page - 1) * result.pagination.per_page + 1,
          to: Math.min(result.pagination.current_page * result.pagination.per_page, result.pagination.total),
        };
      }
      
      // Calculate average rating
      averageRating.value = calculateAverageRating();
      totalReviews.value = pagination.value.total;
    } else {
      throw new Error(result.message || 'Failed to fetch reviews');
    }
  } catch (err) {
    console.error('Failed to fetch reviews:', err);
    error.value = t('reviews.errors.fetchFailed') || 'فشل في جلب المراجعات';
  } finally {
    loading.value = false;
  }
};

/**
 * Handle page change event from pagination
 */
const handlePageChange = (page: number) => {
  if (page < 1 || page > pagination.value.last_page) {
    return;
  }
  
  fetchReviews(page);
};

/**
 * Retry fetching reviews after an error
 */
const retryFetch = () => {
  fetchReviews(pagination.value.current_page);
};

/**
 * Watch for entityId changes and refetch
 */
watch(() => props.entityId, (newId) => {
  if (newId && newId > 0) {
    fetchReviews(1);
  }
});

/**
 * Initialize component
 */
onMounted(() => {
  // Validate props
  if (!['consultant', 'service'].includes(props.entityType)) {
    console.error(`Invalid entityType: ${props.entityType}`);
    error.value = 'Invalid entity type';
    return;
  }

  // Use initial reviews if provided
  if (props.initialReviews) {
    reviews.value = props.initialReviews.data;
    pagination.value = props.initialReviews.meta;
    averageRating.value = calculateAverageRating();
    totalReviews.value = pagination.value.total;
  } else if (props.entityId && props.entityId > 0) {
    // Fetch reviews from API
    fetchReviews(1);
  }
});
</script>

<style scoped>
/* ReviewsSection styling - using plain CSS to avoid Tailwind v4 @apply issues */

.reviews-section {
  width: 100%;
}

/* Error icon animation */
.error-icon {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>
