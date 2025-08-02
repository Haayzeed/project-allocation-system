<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Alert from '@/components/Alert.vue';
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';

// Define props to receive data from the controller
interface Props {
  configs?: Array<{
    id: number;
    key: string;
    value: string;
    description?: string;
    type: string;
    created_at: string;
    updated_at: string;
  }>;
  flash?: {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
  };
  errors?: Record<string, string>;
}

const props = withDefaults(defineProps<Props>(), {
  configs: () => [],
  flash: () => ({}),
  errors: () => ({})
});

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Configurations', href: '/admin/configs' },
];

const typeOptions = [
  { value: 'string', label: 'String' },
  { value: 'integer', label: 'Integer' },
  { value: 'boolean', label: 'Boolean' },
  { value: 'decimal', label: 'Decimal' },
];

const showModal = ref(false);
const isSubmitting = ref(false);

// Alert states
const showSuccessAlert = ref(!!props.flash?.success);
const showErrorAlert = ref(!!props.flash?.error);
const successMessage = ref(props.flash?.success || '');
const errorMessage = ref(props.flash?.error || '');
const formErrors = ref<Record<string, string>>({});
const errorShownTimestamp = ref(0);

const newConfig = ref({ 
  key: '', 
  value: '', 
  description: '',
  type: 'string'
});

const editingConfig = ref<null | typeof newConfig.value & { id: number }>(null);

function openModal() {
  showModal.value = true;
  // Clear any previous errors
  formErrors.value = {};
  showErrorAlert.value = false;
}

function closeModal() {
  showModal.value = false;
  // Reset form
  newConfig.value = { 
    key: '', 
    value: '', 
    description: '',
    type: 'string'
  };
  editingConfig.value = null;
}

function editConfig(config: any) {
  editingConfig.value = { 
    id: config.id,
    key: config.key,
    value: config.value,
    description: config.description || '',
    type: config.type
  };
  newConfig.value = { ...editingConfig.value };
  delete (newConfig.value as any).id;
  showModal.value = true;
  // Clear any previous errors
  formErrors.value = {};
  showErrorAlert.value = false;
}

function dismissSuccessAlert() {
  showSuccessAlert.value = false;
}

function dismissErrorAlert() {
  showErrorAlert.value = false;
}

function submitForm() {
  isSubmitting.value = true;
  formErrors.value = {};
  showErrorAlert.value = false;

  const routeName = editingConfig.value
    ? route('admin.configs.update', { config: editingConfig.value.id })
    : route('admin.configs.store');

  const method = editingConfig.value ? 'put' : 'post';

  router[method](routeName, newConfig.value, {
    onSuccess: () => {
      closeModal();
      isSubmitting.value = false;
    },
    onError: (errors) => {
      formErrors.value = errors;
      errorMessage.value = 'Please check the form for errors and try again.';
      showErrorAlert.value = true;
      isSubmitting.value = false;
    },
    onFinish: () => {
      isSubmitting.value = false;
    }
  });
}

function deleteConfig(configId: number) {
  if (confirm('Are you sure you want to delete this configuration? This action cannot be undone.')) {
    // Clear previous alerts
    showSuccessAlert.value = false;
    showErrorAlert.value = false;
    
    router.delete(route('admin.configs.destroy', { config: configId }), {
      onSuccess: (page: any) => {
        // Check if there's an error in flash messages
        if (page.props.flash?.error) {
          errorMessage.value = page.props.flash.error;
          showErrorAlert.value = true;
        } else {
          successMessage.value = 'Configuration deleted successfully.';
          showSuccessAlert.value = true;
        }
      },
      onError: (errors) => {
        errorMessage.value = 'Failed to delete configuration. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
}

// Watch for new flash messages
watch(() => props.flash?.success, (newSuccess) => {
  if (newSuccess) {
    successMessage.value = newSuccess;
    showSuccessAlert.value = true;
  }
});

// Watch for flash errors
watch(() => props.flash?.error, (newError) => {
  if (newError) {
    errorMessage.value = newError;
    showErrorAlert.value = true;
    errorShownTimestamp.value = Date.now();
  } else {
    showErrorAlert.value = false;
    errorMessage.value = '';
  }
});

// Helper function to get field error
function getFieldError(field: string): string {
  return formErrors.value[field] || '';
}

// Helper function to check if field has error
function hasFieldError(field: string): boolean {
  return !!formErrors.value[field];
}

// Format value display based on type
function formatValue(value: string, type: string): string {
  switch (type) {
    case 'boolean':
      return value === 'true' ? 'Yes' : 'No';
    case 'integer':
    case 'decimal':
      return value;
    default:
      return value.length > 50 ? value.substring(0, 50) + '...' : value;
  }
}

// Get badge color for type
function getTypeBadgeColor(type: string): string {
  switch (type) {
    case 'string': return 'bg-blue-100 text-blue-800';
    case 'integer': return 'bg-green-100 text-green-800';
    case 'boolean': return 'bg-purple-100 text-purple-800';
    case 'decimal': return 'bg-orange-100 text-orange-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">System Configurations</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Configuration
        </button>
      </div>

      <!-- Success Alert -->
      <Alert
        v-if="showSuccessAlert"
        type="success"
        :message="successMessage"
        @dismiss="dismissSuccessAlert"
      />

      <!-- Error Alert -->
      <Alert
        v-if="showErrorAlert"
        type="error"
        :message="errorMessage"
        @dismiss="dismissErrorAlert"
      />

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-md p-6 relative max-h-[90vh] overflow-y-auto">
          <button @click="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
          
          <h3 class="text-lg font-semibold mb-4">{{ editingConfig ? 'Edit Configuration' : 'Add Configuration' }}</h3>
          
          <form @submit.prevent="submitForm" class="space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Key</label>
              <input
                v-model="newConfig.key"
                type="text"
                class="w-full border rounded px-3 py-2"
                :class="{ 'border-red-500': hasFieldError('key') }"
                placeholder="e.g., max_students_per_supervisor"
                required
              />
              <p v-if="hasFieldError('key')" class="text-red-500 text-xs mt-1">{{ getFieldError('key') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Value</label>
              <input
                v-model="newConfig.value"
                type="text"
                class="w-full border rounded px-3 py-2"
                :class="{ 'border-red-500': hasFieldError('value') }"
                placeholder="Configuration value"
                required
              />
              <p v-if="hasFieldError('value')" class="text-red-500 text-xs mt-1">{{ getFieldError('value') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Type</label>
              <select
                v-model="newConfig.type"
                class="w-full border rounded px-3 py-2"
                :class="{ 'border-red-500': hasFieldError('type') }"
                required
              >
                <option v-for="type in typeOptions" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <p v-if="hasFieldError('type')" class="text-red-500 text-xs mt-1">{{ getFieldError('type') }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea
                v-model="newConfig.description"
                rows="3"
                class="w-full border rounded px-3 py-2"
                :class="{ 'border-red-500': hasFieldError('description') }"
                placeholder="Brief description of this configuration"
              ></textarea>
              <p v-if="hasFieldError('description')" class="text-red-500 text-xs mt-1">{{ getFieldError('description') }}</p>
            </div>

            <div class="flex justify-end pt-4">
              <button 
                type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                :disabled="isSubmitting"
              >
                {{ isSubmitting ? 'Processing...' : (editingConfig ? 'Update' : 'Add') }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Key</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Value</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="config in props.configs" :key="config.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ config.key }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white" :title="config.value">
                    {{ formatValue(config.value, config.type) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getTypeBadgeColor(config.type)">
                    {{ config.type }}
                  </span>
                </td>
                <td class="px-6 py-4 max-w-xs">
                  <div class="text-sm text-gray-900 dark:text-white truncate" :title="config.description || 'No description'">
                    {{ config.description || 'No description' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800" title="Edit" @click="editConfig(config)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                      </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-800" title="Delete" @click="deleteConfig(config.id)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="props.configs.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No configurations found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 