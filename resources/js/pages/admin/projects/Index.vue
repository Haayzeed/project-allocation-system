<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import Alert from '@/components/Alert.vue';
import { ref, computed, watch } from 'vue';
import { router, Link } from '@inertiajs/vue3';

// Define props to receive data from the controller
interface Props {
  projects?: Array<{
    id: number;
    title: string;
    description: string;
    objectives?: string;
    methodology?: string;
    status: string;
    created_at: string;
    student: {
      id: number;
      name: string;
      email: string;
      student_id: string;
      level: string;
      session: string;
    };
    department: {
      id: number;
      name: string;
    };
    allocation?: {
      id: number;
      status: string;
      match_score?: number;
      admin_notes?: string;
      supervisor: {
        id: number;
        name: string;
      };
    } | null;
    allocation_status: string;
  }>;
  flash?: {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
  };
}

const props = withDefaults(defineProps<Props>(), {
  projects: () => [],
  flash: () => ({})
});

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Projects', href: '/admin/projects' },
];

// Alert states
const showSuccessAlert = ref(!!props.flash?.success);
const showErrorAlert = ref(!!props.flash?.error);
const showWarningAlert = ref(!!props.flash?.warning);
const showInfoAlert = ref(!!props.flash?.info);
const successMessage = ref(props.flash?.success || '');
const errorMessage = ref(props.flash?.error || '');
const warningMessage = ref(props.flash?.warning || '');
const infoMessage = ref(props.flash?.info || '');

// Loading states
const allocatingProjects = ref<Set<number>>(new Set());
const bulkAllocating = ref(false);

// Watch for new flash messages
watch(() => props.flash?.success, (newSuccess) => {
  if (newSuccess) {
    successMessage.value = newSuccess;
    showSuccessAlert.value = true;
  }
});

watch(() => props.flash?.error, (newError) => {
  if (newError) {
    errorMessage.value = newError;
    showErrorAlert.value = true;
  }
});

watch(() => props.flash?.warning, (newWarning) => {
  if (newWarning) {
    warningMessage.value = newWarning;
    showWarningAlert.value = true;
  }
});

watch(() => props.flash?.info, (newInfo) => {
  if (newInfo) {
    infoMessage.value = newInfo;
    showInfoAlert.value = true;
  }
});

function dismissSuccessAlert() {
  showSuccessAlert.value = false;
}

function dismissErrorAlert() {
  showErrorAlert.value = false;
}

function dismissWarningAlert() {
  showWarningAlert.value = false;
}

function dismissInfoAlert() {
  showInfoAlert.value = false;
}

function allocateSupervisor(project: any) {
  if (allocatingProjects.value.has(project.id)) {
    return; // Already allocating
  }

  allocatingProjects.value.add(project.id);
  
  // Clear previous alerts
  showSuccessAlert.value = false;
  showErrorAlert.value = false;
  showWarningAlert.value = false;
  showInfoAlert.value = false;

  router.post(route('admin.projects.allocate', { project: project.id }), {}, {
    onSuccess: (page: any) => {
      allocatingProjects.value.delete(project.id);
      // The success/error messages will be handled by watchers above
    },
    onError: (errors) => {
      allocatingProjects.value.delete(project.id);
      errorMessage.value = 'Failed to allocate supervisor. Please try again.';
      showErrorAlert.value = true;
    },
    onFinish: () => {
      allocatingProjects.value.delete(project.id);
    }
  });
}

function bulkAllocate() {
  if (bulkAllocating.value) {
    return; // Already allocating
  }

  if (!confirm('Are you sure you want to allocate supervisors for all unallocated projects? This will use AI to match students with the best available supervisors.')) {
    return;
  }

  bulkAllocating.value = true;
  
  // Clear previous alerts
  showSuccessAlert.value = false;
  showErrorAlert.value = false;
  showWarningAlert.value = false;
  showInfoAlert.value = false;

  router.post(route('admin.projects.bulk-allocate'), {}, {
    onSuccess: (page: any) => {
      bulkAllocating.value = false;
      // The success/error messages will be handled by watchers above
    },
    onError: (errors) => {
      bulkAllocating.value = false;
      errorMessage.value = 'Bulk allocation failed. Please try again.';
      showErrorAlert.value = true;
    },
    onFinish: () => {
      bulkAllocating.value = false;
    }
  });
}

// Computed values
const unallocatedProjectsCount = computed(() => {
  return props.projects.filter(p => p.allocation_status === 'Not Allocated').length;
});

const allocatedProjectsCount = computed(() => {
  return props.projects.filter(p => p.allocation_status !== 'Not Allocated').length;
});

const totalProjectsCount = computed(() => {
  return props.projects.length;
});

// Helper functions
function getStatusBadgeColor(status: string): string {
  switch (status.toLowerCase()) {
    case 'not allocated': return 'bg-gray-100 text-gray-800';
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'approved': return 'bg-green-100 text-green-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-blue-100 text-blue-800';
  }
}

function formatMatchScore(score?: number): string {
  return score ? `${Math.round(score)}%` : 'N/A';
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Student Projects</h1>
        <button 
          @click="bulkAllocate" 
          class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded shadow"
          :disabled="bulkAllocating || unallocatedProjectsCount === 0"
          :class="{ 'opacity-50 cursor-not-allowed': bulkAllocating || unallocatedProjectsCount === 0 }"
        >
          {{ bulkAllocating ? 'Processing...' : `Bulk Allocate (${unallocatedProjectsCount})` }}
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Projects</h3>
          <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalProjectsCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Allocated</h3>
          <p class="text-2xl font-bold text-green-600">{{ allocatedProjectsCount }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
          <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Unallocated</h3>
          <p class="text-2xl font-bold text-orange-600">{{ unallocatedProjectsCount }}</p>
        </div>
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

      <!-- Warning Alert -->
      <Alert
        v-if="showWarningAlert"
        type="warning"
        :message="warningMessage"
        @dismiss="dismissWarningAlert"
      />

      <!-- Info Alert -->
      <Alert
        v-if="showInfoAlert"
        type="info"
        :message="infoMessage"
        @dismiss="dismissInfoAlert"
      />

      <!-- Projects Table -->
      <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Supervisor</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Match Score</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="project in props.projects" :key="project.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4">
                  <div class="max-w-xs">
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate" :title="project.title">
                      {{ project.title }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate" :title="project.description">
                      {{ project.description.substring(0, 100) }}{{ project.description.length > 100 ? '...' : '' }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ project.student.name }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ project.student.student_id }}</div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">{{ project.student.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ project.department.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeColor(project.allocation_status)">
                    {{ project.allocation_status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div v-if="project.allocation" class="text-sm text-gray-900 dark:text-white">
                    {{ project.allocation.supervisor.name }}
                  </div>
                  <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                    Not assigned
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ formatMatchScore(project.allocation?.match_score) }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex gap-2">
                    <Link 
                      :href="route('admin.projects.show', { project: project.id })"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded"
                      title="View Details"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </Link>
                    <button
                      v-if="project.allocation_status === 'Not Allocated'"
                      class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
                      @click="allocateSupervisor(project)"
                      :disabled="allocatingProjects.has(project.id)"
                      :class="{ 'opacity-50 cursor-not-allowed': allocatingProjects.has(project.id) }"
                    >
                      {{ allocatingProjects.has(project.id) ? 'Allocating...' : 'Allocate AI' }}
                    </button>
                    <span v-else class="text-sm text-gray-500">
                      Already allocated
                    </span>
                  </div>
                </td>
              </tr>
              <tr v-if="props.projects.length === 0">
                <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                  <div class="text-center">
                    <p class="text-lg">No submitted projects found</p>
                    <p class="text-sm mt-2">Projects will appear here once students submit them for review.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 