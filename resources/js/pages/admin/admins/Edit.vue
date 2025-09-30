<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Admin</h1>

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

        <form @submit.prevent="submitForm" class="space-y-6">
          <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Admin Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input 
                  v-model="form.name" 
                  type="text" 
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('name') }"
                  required 
                />
                <p v-if="hasFieldError('name')" class="text-red-500 text-xs mt-1">{{ getFieldError('name') }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('email') }"
                  required 
                />
                <p v-if="hasFieldError('email')" class="text-red-500 text-xs mt-1">{{ getFieldError('email') }}</p>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3">
            <Link 
              :href="route('admin.admins.index')"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-md transition-colors"
            >
              Cancel
            </Link>
            <button 
              type="submit" 
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors" 
              :disabled="isUpdating"
            >
              {{ isUpdating ? 'Updating...' : 'Update Admin' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';

interface Admin {
  id: number;
  name: string;
  email: string;
  banned_at?: string;
  created_at: string;
}

interface Props {
  admin: Admin;
  flash?: {
    success?: string;
    error?: string;
  };
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Admin Management', href: '/admin/admins' },
  { title: 'Edit Admin', href: `/admin/admins/${props.admin.id}/edit` },
];

const isUpdating = ref(false);
const form = ref({
  name: props.admin.name,
  email: props.admin.email,
});

const formErrors = ref<Record<string, string>>({});

// Alert states
const showSuccessAlert = ref(!!props.flash?.success);
const showErrorAlert = ref(!!props.flash?.error);
const successMessage = ref(props.flash?.success || '');
const errorMessage = ref(props.flash?.error || '');

function submitForm() {
  isUpdating.value = true;
  formErrors.value = {};
  showErrorAlert.value = false;
  
  router.put(route('admin.admins.update', { admin: props.admin.id }), form.value, {
    onSuccess: () => {
      isUpdating.value = false;
    },
    onError: (errors) => {
      formErrors.value = errors;
      errorMessage.value = 'Please check the form for errors and try again.';
      showErrorAlert.value = true;
      isUpdating.value = false;
    },
    onFinish: () => {
      isUpdating.value = false;
    }
  });
}

function getFieldError(field: string): string {
  return formErrors.value[field] || '';
}

function hasFieldError(field: string): boolean {
  return !!formErrors.value[field];
}

function dismissSuccessAlert() {
  showSuccessAlert.value = false;
}

function dismissErrorAlert() {
  showErrorAlert.value = false;
}
</script>
