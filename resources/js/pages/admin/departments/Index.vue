<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Alert from '@/components/Alert.vue';
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';

// Define props to receive data from the controller
interface Props {
  departments?: Array<{
    id: number;
    name: string;
    code: string;
    description?: string;
    students_count: number;
    supervisors_count: number;
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
  departments: () => [],
  flash: () => ({}),
  errors: () => ({})
});

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Departments', href: '/admin/departments' },
];

const showModal = ref(false);
const activeTab = ref<'form' | 'csv'>('form');
const isSubmitting = ref(false);

// Alert states
const showSuccessAlert = ref(!!props.flash?.success);
const showErrorAlert = ref(!!props.flash?.error);
const successMessage = ref(props.flash?.success || '');
const errorMessage = ref(props.flash?.error || '');
const formErrors = ref<Record<string, string>>({});
const errorShownTimestamp = ref(0);

const newDepartment = ref({ 
  name: '', 
  code: '', 
  description: '' 
});
const editingDepartment = ref<null | { 
  id: number; 
  name: string; 
  code: string; 
  description: string; 
}>(null);

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
  newDepartment.value = { name: '', code: '', description: '' };
  editingDepartment.value = null;
  formErrors.value = {};
  showErrorAlert.value = false;
}

function closeModal() {
  showModal.value = false;
  editingDepartment.value = null;
  formErrors.value = {};
  showErrorAlert.value = false;
}

function editDepartment(dept: { id: number; name: string; code: string; description?: string }) {
  editingDepartment.value = { 
    id: dept.id,
    name: dept.name, 
    code: dept.code, 
    description: dept.description || '' 
  };
  newDepartment.value = { 
    name: dept.name, 
    code: dept.code, 
    description: dept.description || '' 
  };
  showModal.value = true;
  activeTab.value = 'form';
  formErrors.value = {};
  showErrorAlert.value = false;
}

function deleteDepartment(departmentId: number) {
  if (confirm('Are you sure you want to delete this department? This action cannot be undone.')) {
    // Clear previous alerts
    showSuccessAlert.value = false;
    showErrorAlert.value = false;
    
    router.delete(route('admin.departments.destroy', { department: departmentId }), {
      onSuccess: (page: any) => {
        // Check if there's an error in flash messages (like validation errors)
        if (page.props.flash?.error) {
          errorMessage.value = page.props.flash.error;
          showErrorAlert.value = true;
        } else {
          successMessage.value = 'Department deleted successfully.';
          showSuccessAlert.value = true;
        }
      },
      onError: (errors) => {
        errorMessage.value = errors.message || 'Failed to delete department. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
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
  
  const routeName = editingDepartment.value 
    ? route('admin.departments.update', { department: editingDepartment.value.id })
    : route('admin.departments.store');
  
  const method = editingDepartment.value ? 'put' : 'post';
  
  router[method](routeName, newDepartment.value, {
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

function handleCSVUpload(event: Event) {
  // Add CSV upload logic here
  closeModal();
}

// Watch for new flash messages
watch(() => props.flash?.success, (newSuccess) => {
  if (newSuccess) {
    successMessage.value = newSuccess;
    showSuccessAlert.value = true;
  }
});

// Watch for flash errors - always show if there's an error and alert is dismissed
watch(() => props.flash?.error, (newError) => {
  if (newError) {
    errorMessage.value = newError;
    showErrorAlert.value = true;
    errorShownTimestamp.value = Date.now();
  } else {
    // Clear error state when no error
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
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Departments</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Department
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
          <div class="flex border-b mb-4">
            <button :class="['flex-1 py-2', activeTab === 'form' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'form'">Form</button>
            <button :class="['flex-1 py-2', activeTab === 'csv' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'csv'">CSV Upload</button>
          </div>
          <div v-if="activeTab === 'form'">
            <form @submit.prevent="submitForm" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input 
                  v-model="newDepartment.name" 
                  type="text" 
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('name') }"
                  required 
                />
                <p v-if="hasFieldError('name')" class="text-red-500 text-xs mt-1">{{ getFieldError('name') }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Code</label>
                <input 
                  v-model="newDepartment.code" 
                  type="text" 
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('code') }"
                  :readonly="!!editingDepartment"
                  required 
                />
                <p v-if="hasFieldError('code')" class="text-red-500 text-xs mt-1">{{ getFieldError('code') }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea 
                  v-model="newDepartment.description" 
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('description') }"
                  rows="3"
                ></textarea>
                <p v-if="hasFieldError('description')" class="text-red-500 text-xs mt-1">{{ getFieldError('description') }}</p>
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded" :disabled="isSubmitting">
                  {{ isSubmitting ? (editingDepartment ? 'Updating...' : 'Adding...') : (editingDepartment ? 'Update' : 'Add') }}
                </button>
              </div>
            </form>
          </div>
          <div v-else>
            <form @submit.prevent="handleCSVUpload">
              <label class="block text-sm font-medium mb-2">Upload CSV File</label>
              <input type="file" accept=".csv" class="mb-4" required />
              <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Upload</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- End Modal -->

      <!-- Departments Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Students</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supervisors</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="dept in props.departments" :key="dept.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ dept.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ dept.code }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="text-sm text-gray-900 dark:text-white">{{ dept.description || 'No description' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ dept.students_count }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ dept.supervisors_count }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800" title="Edit" @click="editDepartment(dept)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                      </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-800" title="Delete" @click="deleteDepartment(dept.id)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="props.departments.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No departments found
                </td>
              </tr>
            </tbody>
          </table>
        </div>


      </div>
    </div>
  </AppLayout>
</template> 