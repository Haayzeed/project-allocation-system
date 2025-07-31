<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';

// Define props to receive data from the controller
interface Props {
  students?: {
    data?: Array<{
      id: number;
      name: string;
      email: string;
      role: string;
      student?: {
        id: number;
        student_id: string;
        level: string;
        session: string;
        department?: {
          id: number;
          name: string;
        };
        allocation?: {
          supervisor?: {
            user?: {
              name: string;
            };
          };
        };
      };
    }>;
    links?: Array<any>;
    meta?: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  departments?: Array<{
    id: number;
    name: string;
    code?: string;
  }>;
}

const props = withDefaults(defineProps<Props>(), {
  students: () => ({
    data: [],
    links: [],
    meta: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0
    }
  }),
  departments: () => []
});

// Computed properties for safe access
const studentsData = computed(() => props.students?.data || []);
const studentsMeta = computed(() => props.students?.meta || {
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
});
const studentsLinks = computed(() => props.students?.links || []);

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Students', href: '/admin/students' },
];

const showModal = ref(false);
const activeTab = ref<'form' | 'csv'>('form');
const newStudent = ref({ 
  name: '', 
  email: '', 
  department_id: '', 
  student_id: '', 
  level: '',
  session: '',
  password: '',
  password_confirmation: ''
});

const levels = ['100', '200', '300', '400', '500', '600', '700'];
const sessions = ['2023/2024', '2024/2025', '2025/2026', '2026/2027', '2027/2028', '2028/2029', '2029/2030'];

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
}
function closeModal() {
  showModal.value = false;
  // Reset form
  newStudent.value = { 
    name: '', 
    email: '', 
    department_id: '', 
    student_id: '', 
    level: '',
    session: '',
    password: '',
    password_confirmation: ''
  };
}
function submitForm() {
  // Add form submission logic here
  closeModal();
}
function handleCSVUpload(event: Event) {
  // Add CSV upload logic here
  closeModal();
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Students</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Student
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Students</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ studentsMeta.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">With Allocation</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ studentsData.filter(s => s.student?.allocation).length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Without Allocation</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ studentsData.filter(s => !s.student?.allocation).length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Current Page</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ studentsMeta.current_page }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg w-full max-w-md p-6 relative">
          <button @click="closeModal" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">&times;</button>
          <div class="flex border-b mb-4">
            <button :class="['flex-1 py-2', activeTab === 'form' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'form'">Form</button>
            <button :class="['flex-1 py-2', activeTab === 'csv' ? 'border-b-2 border-blue-600 font-bold' : '']" @click="activeTab = 'csv'">CSV Upload</button>
          </div>
          <div v-if="activeTab === 'form'">
            <form @submit.prevent="submitForm" class="space-y-4">
              <div>
                <label class="block text-sm font-medium mb-1">Student ID</label>
                <input v-model="newStudent.student_id" type="text" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Name</label>
                <input v-model="newStudent.name" type="text" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Email</label>
                <input v-model="newStudent.email" type="email" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Department</label>
                <select v-model="newStudent.department_id" class="w-full border rounded px-3 py-2" required>
                  <option value="" disabled>Select department</option>
                  <option v-for="dept in props.departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Level</label>
                <select v-model="newStudent.level" class="w-full border rounded px-3 py-2" required>
                  <option value="" disabled>Select level</option>
                  <option v-for="lvl in levels" :key="lvl" :value="lvl">{{ lvl }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Session</label>
                <select v-model="newStudent.session" class="w-full border rounded px-3 py-2" required>
                  <option value="" disabled>Select session</option>
                  <option v-for="sess in sessions" :key="sess" :value="sess">{{ sess }}</option>
                </select>
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Add</button>
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

      <!-- Students Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Session</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Allocation</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="user in studentsData" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ user.student?.department?.name || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ user.student?.student_id || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="user.student?.level" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    {{ user.student.level }}
                  </span>
                  <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    N/A
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ user.student?.session || 'N/A' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="user.student?.allocation" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ user.student.allocation.supervisor?.user?.name || 'Assigned' }}
                  </span>
                  <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                    Not Assigned
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <button class="text-blue-600 hover:text-blue-800" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                      </svg>
                    </button>
                    <button class="text-red-600 hover:text-red-800" title="Delete">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="studentsData.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                  No students found
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="studentsMeta.last_page > 1" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex justify-between flex-1 sm:hidden">
              <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Previous
              </a>
              <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Next
              </a>
            </div>
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700 dark:text-gray-300">
                  Showing
                  <span class="font-medium">{{ ((studentsMeta.current_page - 1) * studentsMeta.per_page) + 1 }}</span>
                  to
                  <span class="font-medium">{{ Math.min(studentsMeta.current_page * studentsMeta.per_page, studentsMeta.total) }}</span>
                  of
                  <span class="font-medium">{{ studentsMeta.total }}</span>
                  results
                </p>
              </div>
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <a v-for="link in studentsLinks" :key="link.label" 
                     :href="link.url" 
                     class="relative inline-flex items-center px-2 py-2 border text-sm font-medium"
                     :class="{
                       'bg-blue-50 border-blue-500 text-blue-600': link.active,
                       'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active && link.url,
                       'bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed': !link.url
                     }"
                     v-html="link.label">
                  </a>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 