<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Alert from '@/components/Alert.vue';
import Multiselect from 'vue-multiselect';
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';

// Define props to receive data from the controller
interface Props {
  supervisors?: Array<{
    id: number;
    name: string;
    email: string;
    supervisor?: {
      id: number;
      title: string;
      staff_id: string;
      bio: string;
      department_id: number;
      specializations: Array<{
        id: number;
        name: string;
        description?: string;
      }>;
      // max_students: number;
      department?: {
        id: number;
        name: string;
      };
    };
  }>;
  departments?: Array<{
    id: number;
    name: string;
    code: string;
  }>;
  specializations?: Array<{
    id: number;
    name: string;
    description?: string;
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
  supervisors: () => [],
  departments: () => [],
  specializations: () => [],
  flash: () => ({}),
  errors: () => ({})
});

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Supervisors', href: '/admin/supervisors' },
];

const titleOptions = [
  { value: 'Mr.', label: 'Mr.' },
  { value: 'Mrs.', label: 'Mrs.' },
  { value: 'Ms.', label: 'Ms.' },
  { value: 'Dr.', label: 'Dr.' },
  { value: 'Prof.', label: 'Prof.' },
  { value: 'Assoc. Prof.', label: 'Assoc. Prof.' },
  { value: 'Asst. Prof.', label: 'Asst. Prof.' },
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

const newSupervisor = ref({ 
  name: '', 
  email: '', 
  title: '',
  staff_id: '',
  department_id: '', 
  bio: '',
  specialization_ids: [] as number[],
  selected_specializations: [] as any[]
});

const editingSupervisor = ref<null | typeof newSupervisor.value & { id: number }>(null);

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
  // Clear any previous errors
  formErrors.value = {};
  showErrorAlert.value = false;
}

function closeModal() {
  showModal.value = false;
  // Reset form
  newSupervisor.value = { 
    name: '', 
    email: '', 
    title: '',
    staff_id: '',
    department_id: '', 
    bio: '',
    specialization_ids: [],
    selected_specializations: []
  };
  editingSupervisor.value = null;
}

function editSupervisor(supervisor: any) {
  const selectedSpecs = supervisor.supervisor?.specializations || [];
  editingSupervisor.value = { 
    id: supervisor.id,
    name: supervisor.name,
    email: supervisor.email,
    title: supervisor.supervisor?.title || '',
    staff_id: supervisor.supervisor?.staff_id || '',
    department_id: supervisor.supervisor?.department_id?.toString() || '',
    bio: supervisor.supervisor?.bio || '',
    specialization_ids: selectedSpecs.map((spec: any) => spec.id),
    selected_specializations: selectedSpecs
  };
  newSupervisor.value = { ...editingSupervisor.value };
  delete (newSupervisor.value as any).id;
  showModal.value = true;
  activeTab.value = 'form';
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

  const routeName = editingSupervisor.value
    ? route('admin.supervisors.update', { supervisor: editingSupervisor.value.id })
    : route('admin.supervisors.store');

  const method = editingSupervisor.value ? 'put' : 'post';

  router[method](routeName, newSupervisor.value, {
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

function deleteSupervisor(supervisor: any) {
  if (confirm('Are you sure you want to delete this supervisor? This action cannot be undone.')) {
    // Clear previous alerts
    showSuccessAlert.value = false;
    showErrorAlert.value = false;
    
    router.delete(route('admin.supervisors.destroy', { supervisor: supervisor.id }), {
      onSuccess: (page: any) => {
        // Check if there's an error in flash messages (like validation errors)
        if (page.props.flash?.error) {
          errorMessage.value = page.props.flash.error;
          showErrorAlert.value = true;
        } else {
          successMessage.value = 'Supervisor deleted successfully.';
          showSuccessAlert.value = true;
        }
      },
      onError: (errors) => {
        errorMessage.value = 'Failed to delete supervisor. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
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

// Update specialization IDs when multiselect changes
function onSpecializationsChange(selectedSpecs: any[]) {
  newSupervisor.value.selected_specializations = selectedSpecs;
  newSupervisor.value.specialization_ids = selectedSpecs.map(spec => spec.id);
}


</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Supervisors</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Supervisor
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
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
          <button @click="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
          <div class="flex border-b mb-4">
            <button :class="['flex-1 py-2', activeTab === 'form' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'form'">Form</button>
            <button :class="['flex-1 py-2', activeTab === 'csv' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'csv'">CSV Upload</button>
          </div>

          <!-- Form Tab -->
          <div v-if="activeTab === 'form'">
            <form @submit.prevent="submitForm" class="space-y-4">
              <!-- Two Column Layout -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Left Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium mb-1">Title</label>
                    <select
                      v-model="newSupervisor.title"
                      class="w-full border rounded px-3 py-2"
                      :class="{ 'border-red-500': hasFieldError('title') }"
                      required
                    >
                      <option value="" disabled>Select title</option>
                      <option v-for="titleOption in titleOptions" :key="titleOption.value" :value="titleOption.value">
                        {{ titleOption.label }}
                      </option>
                    </select>
                    <p v-if="hasFieldError('title')" class="text-red-500 text-xs mt-1">{{ getFieldError('title') }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1">Staff ID</label>
                    <input
                      v-model="newSupervisor.staff_id"
                      type="text"
                      class="w-full border rounded px-3 py-2"
                      :class="{ 'border-red-500': hasFieldError('staff_id') }"
                      required
                    />
                    <p v-if="hasFieldError('staff_id')" class="text-red-500 text-xs mt-1">{{ getFieldError('staff_id') }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <select
                      v-model="newSupervisor.department_id"
                      class="w-full border rounded px-3 py-2"
                      :class="{ 'border-red-500': hasFieldError('department_id') }"
                      required
                    >
                      <option value="" disabled>Select department</option>
                      <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                    </select>
                    <p v-if="hasFieldError('department_id')" class="text-red-500 text-xs mt-1">{{ getFieldError('department_id') }}</p>
                  </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium mb-1">Name</label>
                    <input
                      v-model="newSupervisor.name"
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
                      v-model="newSupervisor.email"
                      type="email"
                      class="w-full border rounded px-3 py-2"
                      :class="{ 'border-red-500': hasFieldError('email') }"
                      required
                    />
                    <p v-if="hasFieldError('email')" class="text-red-500 text-xs mt-1">{{ getFieldError('email') }}</p>
                  </div>


                </div>
              </div>

              <!-- Full Width Fields -->
              <div>
                <label class="block text-sm font-medium mb-1">Bio</label>
                <textarea
                  v-model="newSupervisor.bio"
                  rows="3"
                  class="w-full border rounded px-3 py-2"
                  :class="{ 'border-red-500': hasFieldError('bio') }"
                  placeholder="Enter supervisor's bio and qualifications..."
                ></textarea>
                <p v-if="hasFieldError('bio')" class="text-red-500 text-xs mt-1">{{ getFieldError('bio') }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium mb-1">Specializations</label>
                <Multiselect
                  v-model="newSupervisor.selected_specializations"
                  :options="props.specializations"
                  :multiple="true"
                  :close-on-select="false"
                  :clear-on-select="false"
                  :preserve-search="true"
                  placeholder="Search and select specializations"
                  label="name"
                  track-by="id"
                  :searchable="true"
                  :loading="false"
                  :internal-search="true"
                  :max-height="150"
                  :show-no-results="false"
                  :hide-selected="true"
                  @update:model-value="onSpecializationsChange"
                  :class="{ 'border-red-500': hasFieldError('specialization_ids') }"
                />
                <small class="text-xs text-gray-500">Search and select multiple specializations</small>
                <p v-if="hasFieldError('specialization_ids')" class="text-red-500 text-xs mt-1">{{ getFieldError('specialization_ids') }}</p>
              </div>

              <div class="flex justify-end pt-4">
                <button 
                  type="submit" 
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                  :disabled="isSubmitting"
                >
                  {{ isSubmitting ? 'Processing...' : (editingSupervisor ? 'Update' : 'Add') }}
                </button>
              </div>
            </form>
          </div>

          <!-- CSV Tab -->
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

      <!-- Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Staff ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bio</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Specializations</th>
                <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Max Students</th> -->
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="supervisor in props.supervisors" :key="supervisor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">
                    {{ supervisor.supervisor?.title ? `${supervisor.supervisor.title} ${supervisor.name}` : supervisor.name }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ supervisor.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ supervisor.supervisor?.staff_id || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ supervisor.supervisor?.department?.name || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 max-w-xs">
                  <div class="text-sm text-gray-900 dark:text-white truncate" :title="supervisor.supervisor?.bio || 'No bio provided'">
                    {{ supervisor.supervisor?.bio ? (supervisor.supervisor.bio.length > 50 ? supervisor.supervisor.bio.substring(0, 50) + '...' : supervisor.supervisor.bio) : 'No bio' }}
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="specialization in supervisor.supervisor?.specializations || []" 
                      :key="specialization.id"
                      class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"
                    >
                      {{ specialization.name }}
                    </span>
                    <span v-if="!supervisor.supervisor?.specializations?.length" class="text-gray-500 text-sm">N/A</span>
                  </div>
                </td>
                <!-- <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ supervisor.supervisor?.max_students || 'N/A' }}</div>
                </td> -->
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800" title="Edit" @click="editSupervisor(supervisor)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                      </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-800" title="Delete" @click="deleteSupervisor(supervisor)">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="props.supervisors.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No supervisors found
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style src="vue-multiselect/dist/vue-multiselect.css"></style>

<style scoped>
/* Custom styles for multiselect to match our theme */
.multiselect {
  min-height: 42px;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
}

.multiselect.border-red-500 {
  border-color: #ef4444;
}

.multiselect__tags {
  min-height: 40px;
  padding: 8px 40px 0 8px;
  border-radius: 0.375rem;
  border: none;
}

.multiselect__tag {
  background: #3b82f6;
  color: white;
  font-size: 12px;
  font-weight: 500;
  border-radius: 0.25rem;
  padding: 4px 8px;
  margin-bottom: 4px;
  margin-right: 4px;
}

.multiselect__tag-icon:hover {
  background: #1d4ed8;
}

.multiselect__input {
  background: transparent;
  padding: 4px 0;
}

.multiselect__placeholder {
  color: #9ca3af;
  padding-top: 4px;
}

.multiselect__content-wrapper {
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  border-top: none;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}

.multiselect__option {
  padding: 8px 12px;
  min-height: auto;
  line-height: 1.25rem;
  color: #374151;
}

.multiselect__option--highlight {
  background: #3b82f6;
  color: white;
}

.multiselect__option--selected {
  background: #f3f4f6;
  color: #374151;
  font-weight: 500;
}

.multiselect__option--selected.multiselect__option--highlight {
  background: #1d4ed8;
  color: white;
}

.multiselect__spinner {
  border-top-color: #3b82f6;
}
</style> 