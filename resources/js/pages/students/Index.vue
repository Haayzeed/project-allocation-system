<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const breadcrumbs = [
  { title: 'Student Dashboard', href: '/students' },
];

const page = usePage();
const user = computed<any>(() => (page.props as any).user ?? null);

const studentName = computed(() => user.value?.name ?? '');
const studentMatric = computed(() => user.value?.student?.student_id ?? '');
const studentDepartment = computed(() => user.value?.student?.department?.name ?? '');
const studentLevel = computed(() => (user.value?.student?.level ?? '').toString());

const sortedProjects = computed(() => {
  const projects = (user.value?.student?.projects as any[]) ?? [];
  return [...projects].sort((a, b) => {
    const aTime = a?.created_at ? new Date(a.created_at).getTime() : 0;
    const bTime = b?.created_at ? new Date(b.created_at).getTime() : 0;
    return bTime - aTime;
  });
});

const currentProject = computed(() => sortedProjects.value[0] ?? null);
const hasSubmittedProject = computed(() => !!currentProject.value && currentProject.value.status !== 'draft');
const projectTitle = computed(() => (hasSubmittedProject.value ? (currentProject.value?.title ?? '') : ''));
const projectDescription = computed(() => (hasSubmittedProject.value ? (currentProject.value?.description ?? '') : ''));
const projectStatus = computed(() => (hasSubmittedProject.value ? (currentProject.value?.status ?? '') : 'Not Submitted'));
const projectSubmittedAt = computed(() => (hasSubmittedProject.value ? (currentProject.value?.created_at ?? '') : ''));

const allocation = computed(() => user.value?.student?.allocation ?? null);
const hasSupervisor = computed(() => !!allocation.value?.supervisor && allocation.value?.status === 'approved');
const supervisorName = computed(() => (hasSupervisor.value ? (allocation.value?.supervisor?.user?.name ?? '') : ''));
const supervisorEmail = computed(() => (hasSupervisor.value ? (allocation.value?.supervisor?.user?.email ?? '') : ''));
const supervisorDepartment = computed(() => (hasSupervisor.value ? (allocation.value?.supervisor?.department?.name ?? '') : ''));
const supervisorSpecs = computed(() => (hasSupervisor.value ? (allocation.value?.supervisor?.specializations ?? []) : []));

// Modal state
const showModal = ref(false);
const isSubmitting = ref(false);
const form = ref({
  title: '',
  description: '',
  objectives: '',
  methodology: ''
});

const openModal = () => {
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.value = {
    title: '',
    description: '',
    objectives: '',
    methodology: ''
  };
};

const submitProject = () => {
  isSubmitting.value = true;
  
  router.post('/students/projects', form.value, {
    onSuccess: () => {
      closeModal();
      isSubmitting.value = false;
    },
    onError: () => {
      isSubmitting.value = false;
    }
  });
};
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6 flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ studentName }}</h1>
          <p class="text-gray-600 dark:text-gray-400">Student Dashboard</p>
        </div>
        <button 
          @click="openModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          Submit Project Topic
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Student Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Student Information</h2>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Name:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ studentName || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Matric Number:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ studentMatric || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Department:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ studentDepartment || '-' }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Level:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ studentLevel || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Project Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Project Details</h2>
          <div class="space-y-3">
            <div>
              <span class="text-gray-600 dark:text-gray-400">Title:</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ projectTitle || '-' }}</p>
            </div>
            <div>
              <span class="text-gray-600 dark:text-gray-400">Description:</span>
              <p class="text-gray-900 dark:text-white">{{ projectDescription || '-' }}</p>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-600 dark:text-gray-400">Status:</span>
              <span class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="projectStatus === 'Not Submitted' ? 'bg-gray-100 text-gray-800' : (projectStatus === 'submitted' || projectStatus === 'approved' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800')">
                {{ projectStatus }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Submitted:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ projectSubmittedAt || '-' }}</span>
            </div>
          </div>
        </div>

        <!-- Supervisor Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Allocated Supervisor</h2>
          <div v-if="hasSupervisor" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Name:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ supervisorName }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Email:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ supervisorEmail }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Department:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ supervisorDepartment || '-' }}</span>
              </div>
            </div>
            <div>
              <span class="text-gray-600 dark:text-gray-400">Specializations:</span>
              <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="spec in supervisorSpecs" :key="spec.id"
                      class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                  {{ spec.name }}
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-sm text-gray-600 dark:text-gray-300">
            Yet to be allocated a supervisor.
          </div>
        </div>
      </div>
    </div>

    <!-- Project Submission Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Submit Project Topic</h3>
            <button 
              @click="closeModal"
              class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <form @submit.prevent="submitProject" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Project Title *
              </label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Enter your project title"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Project Description *
              </label>
              <textarea
                v-model="form.description"
                required
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Describe your project in detail"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Objectives
              </label>
              <textarea
                v-model="form.objectives"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="What are the main objectives of your project?"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Methodology
              </label>
              <textarea
                v-model="form.methodology"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Describe the methodology you plan to use"
              ></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-600 hover:bg-gray-200 dark:hover:bg-gray-500 rounded-md transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="isSubmitting"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors"
              >
                {{ isSubmitting ? 'Submitting...' : 'Submit Project' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 