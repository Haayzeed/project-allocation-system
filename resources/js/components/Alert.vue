<script setup lang="ts">
import { ref, computed } from 'vue';

interface Props {
  type?: 'success' | 'error' | 'warning' | 'info';
  message: string;
  dismissible?: boolean;
  show?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  dismissible: true,
  show: true
});

const emit = defineEmits<{
  dismiss: []
}>();

const isVisible = ref(props.show);

const alertClasses = computed(() => {
  const base = 'border rounded-lg p-4 flex items-center justify-between mb-4';
  
  switch (props.type) {
    case 'success':
      return `${base} bg-green-50 border-green-200 text-green-800`;
    case 'error':
      return `${base} bg-red-50 border-red-200 text-red-800`;
    case 'warning':
      return `${base} bg-yellow-50 border-yellow-200 text-yellow-800`;
    case 'info':
    default:
      return `${base} bg-blue-50 border-blue-200 text-blue-800`;
  }
});

const iconClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-600';
    case 'error':
      return 'text-red-600';
    case 'warning':
      return 'text-yellow-600';
    case 'info':
    default:
      return 'text-blue-600';
  }
});

const dismissButtonClasses = computed(() => {
  switch (props.type) {
    case 'success':
      return 'text-green-600 hover:text-green-800';
    case 'error':
      return 'text-red-600 hover:text-red-800';
    case 'warning':
      return 'text-yellow-600 hover:text-yellow-800';
    case 'info':
    default:
      return 'text-blue-600 hover:text-blue-800';
  }
});

function dismiss() {
  isVisible.value = false;
  emit('dismiss');
}
</script>

<template>
  <div v-if="isVisible" :class="alertClasses">
    <div class="flex items-center">
      <!-- Success Icon -->
      <svg v-if="type === 'success'" class="w-5 h-5 mr-2" :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      
      <!-- Error Icon -->
      <svg v-else-if="type === 'error'" class="w-5 h-5 mr-2" :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      
      <!-- Warning Icon -->
      <svg v-else-if="type === 'warning'" class="w-5 h-5 mr-2" :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
      </svg>
      
      <!-- Info Icon -->
      <svg v-else class="w-5 h-5 mr-2" :class="iconClasses" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      
      <p class="font-medium">{{ message }}</p>
    </div>
    
    <button v-if="dismissible" @click="dismiss" :class="dismissButtonClasses">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>
</template> 