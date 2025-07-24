<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: '/students',
    },
];

// Mock data for demonstration
const students = [
  { name: 'John Doe', email: 'john@example.com', department: 'Computer Science', matricNumber: 'CSC001', level: '400' },
  { name: 'Jane Smith', email: 'jane@example.com', department: 'Mathematics', matricNumber: 'MTH002', level: '300' },
  { name: 'Alice Johnson', email: 'alice@example.com', department: 'Physics', matricNumber: 'PHY003', level: '200' },
];

// Form state
const newStudent = ref({
  name: '',
  email: '',
  department: '',
  matricNumber: '',
  level: '',
});

const showModal = ref(false);
const activeTab = ref<'form' | 'csv'>('form');

const departments = [
  'Computer Science',
  'Mathematics',
  'Physics',
  'Chemistry',
  'Biology',
  'Engineering',
  'Economics',
  'English',
];
const levels = ['100', '200', '300', '400'];

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
}
function closeModal() {
  showModal.value = false;
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
              <label class="block text-sm font-medium mb-1">Name</label>
              <input v-model="newStudent.name" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Email</label>
              <input v-model="newStudent.email" type="email" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Department</label>
              <select v-model="newStudent.department" class="w-full border rounded px-3 py-2" required>
                <option value="" disabled>Select department</option>
                <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Matric Number</label>
              <input v-model="newStudent.matricNumber" type="text" class="w-full border rounded px-3 py-2" required />
            </div>
            <div>
              <label class="block text-sm font-medium mb-1">Level</label>
              <select v-model="newStudent.level" class="w-full border rounded px-3 py-2" required>
                <option value="" disabled>Select level</option>
                <option v-for="lvl in levels" :key="lvl" :value="lvl">{{ lvl }}</option>
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
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matric Number</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
          <tr v-for="student in students" :key="student.matricNumber">
            <td class="px-6 py-4 whitespace-nowrap">{{ student.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ student.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ student.department }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ student.matricNumber }}</td>
            <td class="px-6 py-4 whitespace-nowrap">{{ student.level }}</td>
            <td class="px-6 py-4 whitespace-nowrap flex gap-2">
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
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</AppLayout>
</template> 