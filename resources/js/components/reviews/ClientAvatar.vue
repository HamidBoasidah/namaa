<template>
  <div class="client-avatar" :class="sizeClasses">
    <!-- Image Avatar -->
    <img
      v-if="avatarUrl && !imageError"
      :src="avatarUrl"
      :alt="name"
      class="avatar-image rounded-full object-cover w-full h-full"
      @error="handleImageError"
    />
    
    <!-- Initials Fallback -->
    <div
      v-else
      class="avatar-initials rounded-full flex items-center justify-center font-semibold bg-gradient-to-br from-brand-500 to-brand-700 text-white dark:from-brand-600 dark:to-brand-800"
      :class="initialsTextClass"
    >
      {{ initials }}
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

/**
 * ClientAvatar Component
 * 
 * Displays a client avatar image with fallback to initials when:
 * - No image URL is provided
 * - Image fails to load
 * 
 * Features:
 * - Multiple sizes (sm, md, lg)
 * - Automatic initials generation from name
 * - Error handling for failed image loads
 * - Dark mode support
 * - Responsive design
 * 
 * Requirements: 2.2, 2.6, 5.5
 */

interface ClientAvatarProps {
  src: string | null;
  name: string;
  size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<ClientAvatarProps>(), {
  size: 'md',
});

// Track image loading errors
const imageError = ref(false);

/**
 * Computed avatar URL
 * Returns the src prop if available
 * Requirements: 2.2
 */
const avatarUrl = computed(() => props.src);

/**
 * Generate initials from client name
 * Takes the first letter of the first two words
 * Requirements: 2.6
 */
const initials = computed(() => {
  if (!props.name) return '?';
  
  const words = props.name.trim().split(/\s+/);
  
  if (words.length === 1) {
    // Single word: take first two characters
    return words[0].substring(0, 2).toUpperCase();
  }
  
  // Multiple words: take first letter of first two words
  return words
    .slice(0, 2)
    .map(word => word[0])
    .join('')
    .toUpperCase();
});

/**
 * Handle image loading errors
 * Sets imageError flag to show initials fallback
 * Requirements: 2.6
 */
const handleImageError = () => {
  imageError.value = true;
};

/**
 * Size classes for the avatar container
 * Requirements: 5.5
 */
const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'w-8 h-8';
    case 'lg':
      return 'w-16 h-16';
    default:
      return 'w-12 h-12';
  }
});

/**
 * Text size classes for initials
 * Requirements: 5.5
 */
const initialsTextClass = computed(() => {
  switch (props.size) {
    case 'sm':
      return 'text-xs';
    case 'lg':
      return 'text-2xl';
    default:
      return 'text-base';
  }
});
</script>

<style scoped>
/* Avatar styling - using plain CSS */

.client-avatar {
  flex-shrink: 0;
  overflow: hidden;
}

.avatar-image {
  transition: opacity 0.2s ease;
}

.avatar-initials {
  user-select: none;
  width: 100%;
  height: 100%;
  transition: background-color 0.2s ease;
}
</style>
