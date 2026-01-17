<template>
  <nav 
    class="pagination flex items-center justify-between gap-4 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700 transition-colors duration-200"
    role="navigation"
    :aria-label="t('reviews.section')"
  >
    <!-- Previous Button -->
    <button
      @click="handlePageChange(currentPage - 1)"
      :disabled="isFirstPage"
      :aria-label="t('pagination.previous')"
      class="pagination-btn prev flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
      :class="[
        isFirstPage
          ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
          : 'bg-brand-500 hover:bg-brand-600 text-white dark:bg-brand-600 dark:hover:bg-brand-700 cursor-pointer'
      ]"
    >
      <!-- Left Arrow Icon (RTL aware) -->
      <svg
        class="w-4 h-4 rtl:rotate-180"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
          clip-rule="evenodd"
        />
      </svg>
      <span>{{ t('pagination.previous') }}</span>
    </button>

    <!-- Page Numbers -->
    <div class="pagination-pages flex items-center gap-1 md:gap-2 flex-wrap justify-center">
      <!-- First Page -->
      <button
        v-if="showFirstPage"
        @click="handlePageChange(1)"
        :aria-label="t('pagination.goToPage', { page: 1 })"
        :aria-current="currentPage === 1 ? 'page' : undefined"
        class="pagination-btn page w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-sm font-medium rounded-lg transition-all duration-200"
        :class="pageButtonClass(1)"
      >
        1
      </button>

      <!-- First Ellipsis -->
      <span
        v-if="showFirstEllipsis"
        class="pagination-ellipsis text-gray-500 dark:text-gray-400 px-1"
        aria-hidden="true"
      >
        ...
      </span>

      <!-- Visible Page Numbers -->
      <button
        v-for="page in visiblePages"
        :key="page"
        @click="handlePageChange(page)"
        :aria-label="t('pagination.goToPage', { page })"
        :aria-current="page === currentPage ? 'page' : undefined"
        class="pagination-btn page w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-sm font-medium rounded-lg transition-all duration-200"
        :class="pageButtonClass(page)"
      >
        {{ page }}
      </button>

      <!-- Last Ellipsis -->
      <span
        v-if="showLastEllipsis"
        class="pagination-ellipsis text-gray-500 dark:text-gray-400 px-1"
        aria-hidden="true"
      >
        ...
      </span>

      <!-- Last Page -->
      <button
        v-if="showLastPage"
        @click="handlePageChange(totalPages)"
        :aria-label="t('pagination.goToPage', { page: totalPages })"
        :aria-current="currentPage === totalPages ? 'page' : undefined"
        class="pagination-btn page w-8 h-8 md:w-10 md:h-10 flex items-center justify-center text-sm font-medium rounded-lg transition-all duration-200"
        :class="pageButtonClass(totalPages)"
      >
        {{ totalPages }}
      </button>
    </div>

    <!-- Next Button -->
    <button
      @click="handlePageChange(currentPage + 1)"
      :disabled="isLastPage"
      :aria-label="t('pagination.next')"
      class="pagination-btn next flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200"
      :class="[
        isLastPage
          ? 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
          : 'bg-brand-500 hover:bg-brand-600 text-white dark:bg-brand-600 dark:hover:bg-brand-700 cursor-pointer'
      ]"
    >
      <span>{{ t('pagination.next') }}</span>
      <!-- Right Arrow Icon (RTL aware) -->
      <svg
        class="w-4 h-4 rtl:rotate-180"
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
      >
        <path
          fill-rule="evenodd"
          d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
          clip-rule="evenodd"
        />
      </svg>
    </button>
  </nav>

  <!-- Pagination Info (Optional - shows "Showing X to Y of Z") -->
  <div
    v-if="showInfo"
    class="pagination-info text-center text-sm text-gray-600 dark:text-gray-400 mt-3"
  >
    {{ t('pagination.showing', { from, to, total }) }}
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

/**
 * ReviewsPagination Component
 * 
 * Provides pagination controls for navigating through pages of reviews.
 * 
 * Features:
 * - Previous/Next buttons with disabled states
 * - Page number buttons with smart visibility (shows subset of pages)
 * - First/Last page buttons with ellipsis
 * - ARIA labels for accessibility
 * - RTL/LTR support
 * - Dark mode support
 * - Responsive design
 * 
 * Requirements: 4.5, 4.6, 4.7
 */

interface ReviewsPaginationProps {
  currentPage: number;
  totalPages: number;
  perPage: number;
  total: number;
  showInfo?: boolean; // Show "Showing X to Y of Z" text
  maxVisiblePages?: number; // Maximum number of page buttons to show
}

const props = withDefaults(defineProps<ReviewsPaginationProps>(), {
  showInfo: true,
  maxVisiblePages: 5,
});

interface ReviewsPaginationEmits {
  (e: 'page-change', page: number): void;
}

const emit = defineEmits<ReviewsPaginationEmits>();

const { t } = useI18n();

/**
 * Check if current page is the first page
 * Requirements: 4.6
 */
const isFirstPage = computed(() => props.currentPage === 1);

/**
 * Check if current page is the last page
 * Requirements: 4.7
 */
const isLastPage = computed(() => props.currentPage === props.totalPages);

/**
 * Calculate the "from" number for pagination info
 */
const from = computed(() => {
  if (props.total === 0) return 0;
  return (props.currentPage - 1) * props.perPage + 1;
});

/**
 * Calculate the "to" number for pagination info
 */
const to = computed(() => {
  const calculatedTo = props.currentPage * props.perPage;
  return Math.min(calculatedTo, props.total);
});

/**
 * Calculate which page numbers should be visible
 * This creates a smart pagination that shows a subset of pages
 * 
 * Logic:
 * - Always try to show maxVisiblePages buttons
 * - Keep current page in the middle when possible
 * - Show first and last pages separately with ellipsis
 * 
 * Requirements: 4.5
 */
const visiblePages = computed(() => {
  const pages: number[] = [];
  const { currentPage, totalPages, maxVisiblePages } = props;

  // If total pages is less than or equal to max visible, show all
  if (totalPages <= maxVisiblePages) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
    return pages;
  }

  // Calculate the range of pages to show
  let startPage = Math.max(2, currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages - 1, startPage + maxVisiblePages - 1);

  // Adjust start if we're near the end
  if (endPage === totalPages - 1) {
    startPage = Math.max(2, endPage - maxVisiblePages + 1);
  }

  // Build the array of visible pages (excluding first and last)
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }

  return pages;
});

/**
 * Determine if we should show the first page button separately
 */
const showFirstPage = computed(() => {
  return props.totalPages > 1 && !visiblePages.value.includes(1);
});

/**
 * Determine if we should show the last page button separately
 */
const showLastPage = computed(() => {
  return props.totalPages > 1 && !visiblePages.value.includes(props.totalPages);
});

/**
 * Determine if we should show ellipsis before visible pages
 */
const showFirstEllipsis = computed(() => {
  return showFirstPage.value && visiblePages.value.length > 0 && visiblePages.value[0] > 2;
});

/**
 * Determine if we should show ellipsis after visible pages
 */
const showLastEllipsis = computed(() => {
  return (
    showLastPage.value &&
    visiblePages.value.length > 0 &&
    visiblePages.value[visiblePages.value.length - 1] < props.totalPages - 1
  );
});

/**
 * Get CSS classes for a page button
 * Highlights the current page
 */
const pageButtonClass = (page: number) => {
  if (page === props.currentPage) {
    return 'bg-brand-500 text-white dark:bg-brand-600 cursor-default';
  }
  return 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-300 cursor-pointer';
};

/**
 * Handle page change event
 * Emits the page-change event with the new page number
 * Requirements: 4.5
 */
const handlePageChange = (page: number) => {
  // Validate page number
  if (page < 1 || page > props.totalPages || page === props.currentPage) {
    return;
  }

  emit('page-change', page);
};
</script>

<style scoped>
/* Pagination styling - using plain CSS */

.pagination {
  transition: all 0.2s ease;
}

.pagination:hover {
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
}

.pagination-btn {
  transition: all 0.2s ease;
}

.pagination-btn:disabled {
  opacity: 0.5;
}

.pagination-ellipsis {
  user-select: none;
}

@media (max-width: 768px) {
  .pagination {
    flex-wrap: wrap;
  }

  .pagination-btn.prev,
  .pagination-btn.next {
    padding: 0.5rem 0.75rem;
    font-size: 0.75rem;
  }

  .pagination-btn.page {
    width: 2rem;
    height: 2rem;
    font-size: 0.75rem;
  }
}
</style>
