/**
 * Review Types
 * 
 * Type definitions for the Reviews Frontend Display feature
 * Requirements: 10.3, 10.4
 */

/**
 * Review interface representing a single review
 */
export interface Review {
  id: number;
  client_name: string;
  client_avatar: string | null;
  rating: number; // 1-5
  comment: string;
  created_at: string; // ISO 8601 format
}

/**
 * Pagination metadata
 */
export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

/**
 * Paginated reviews response
 */
export interface PaginatedReviews {
  data: Review[];
  meta: PaginationMeta;
}

/**
 * API response format for reviews
 */
export interface ReviewsApiResponse {
  data: Review[];
  meta: PaginationMeta;
  summary: {
    average_rating: number;
    total_reviews: number;
  };
}

/**
 * Props for ReviewsSection component
 */
export interface ReviewsSectionProps {
  entityType: 'consultant' | 'service';
  entityId: number;
  initialReviews?: PaginatedReviews;
}

/**
 * State for reviews management
 */
export interface ReviewsState {
  reviews: Review[];
  pagination: PaginationMeta;
  loading: boolean;
  error: string | null;
  averageRating: number;
  totalReviews: number;
}
