<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Departments', href: '/departments' },
];

// Mock data for demonstration
const departments = ref([
  { name: 'Computer Science', code: 'CSC', description: 'Department of Computer Science' },
  { name: 'Mathematics', code: 'MTH', description: 'Department of Mathematics' },
  { name: 'Physics', code: 'PHY', description: 'Department of Physics' },
]);

const showModal = ref(false);
const activeTab = ref<'form' | 'csv'>('form');
const newDepartment = ref({ name: '', code: '', description: '' });
const editingDepartment = ref<null | { name: string; code: string; description: string }>(null);

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
}
function closeModal() {
  showModal.value = false;
  editingDepartment.value = null;
}
function editDepartment(dept: { name: string; code: string; description: string }) {
  editingDepartment.value = { ...dept };
  newDepartment.value = { ...dept };
  showModal.value = true;
  activeTab.value = 'form';
}
function deleteDepartment(code: string) {
  departments.value = departments.value.filter(d => d.code !== code);
}
function submitForm() {
  if (editingDepartment.value) {
    // Update department
    const idx = departments.value.findIndex(d => d.code === editingDepartment.value!.code);
    if (idx !== -1) {
      departments.value[idx] = { ...newDepartment.value };
    }
    editingDepartment.value = null;
  } else {
    // Add new department
    departments.value.push({ ...newDepartment.value });
  }
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
        <h1 class="text-2xl font-bold">Departments</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Department
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
                <input v-model="newDepartment.name" type="text" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Code</label>
                <input v-model="newDepartment.code" type="text" class="w-full border rounded px-3 py-2" required :readonly="!!editingDepartment" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Description</label>
                <textarea v-model="newDepartment.description" class="w-full border rounded px-3 py-2" rows="2"></textarea>
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">{{ editingDepartment ? 'Update' : 'Add' }}</button>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
            <tr v-for="dept in departments" :key="dept.code">
              <td class="px-6 py-4 whitespace-nowrap">{{ dept.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ dept.code }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ dept.description }}</td>
              <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                <button class="text-blue-600 hover:text-blue-800" title="Edit" @click="editDepartment(dept)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                  </svg>
                </button>
                <button class="text-red-600 hover:text-red-800" title="Delete" @click="deleteDepartment(dept.code)">
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