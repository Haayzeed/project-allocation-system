<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Supervisors', href: '/supervisors' },
];

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
const specializations = [
  'Artificial Intelligence',
  'Data Science',
  'Software Engineering',
  'Algebra',
  'Quantum Physics',
  'Organic Chemistry',
  'Microbiology',
  'Thermodynamics',
];

// Mock data for demonstration
const supervisors = ref([
  { name: 'Dr. John Doe', department: 'Computer Science', specializations: ['Artificial Intelligence', 'Data Science'] },
  { name: 'Prof. Jane Smith', department: 'Mathematics', specializations: ['Algebra'] },
  { name: 'Dr. Alice Johnson', department: 'Physics', specializations: ['Quantum Physics', 'Thermodynamics'] },
]);

const showModal = ref(false);
const activeTab = ref<'form' | 'csv'>('form');
const newSupervisor = ref({ name: '', department: '', specializations: [] as string[] });
const editingSupervisor = ref<null | { name: string; department: string; specializations: string[] }>(null);

function openModal() {
  showModal.value = true;
  activeTab.value = 'form';
  newSupervisor.value = { name: '', department: '', specializations: [] };
}
function closeModal() {
  showModal.value = false;
  editingSupervisor.value = null;
}
function editSupervisor(sup: { name: string; department: string; specializations: string[] }) {
  editingSupervisor.value = { ...sup, specializations: [...sup.specializations] };
  newSupervisor.value = { ...sup, specializations: [...sup.specializations] };
  showModal.value = true;
  activeTab.value = 'form';
}
function deleteSupervisor(name: string, department: string) {
  supervisors.value = supervisors.value.filter(s => !(s.name === name && s.department === department));
}
function submitForm() {
  if (editingSupervisor.value) {
    // Update supervisor
    const idx = supervisors.value.findIndex(s => s.name === editingSupervisor.value!.name && s.department === editingSupervisor.value!.department);
    if (idx !== -1) {
      supervisors.value[idx] = { ...newSupervisor.value };
    }
    editingSupervisor.value = null;
  } else {
    // Add new supervisor
    supervisors.value.push({ ...newSupervisor.value });
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
        <h1 class="text-2xl font-bold">Supervisors</h1>
        <button @click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
          Add Supervisor
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
                <input v-model="newSupervisor.name" type="text" class="w-full border rounded px-3 py-2" required />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Department</label>
                <select v-model="newSupervisor.department" class="w-full border rounded px-3 py-2" required :disabled="!!editingSupervisor">
                  <option value="" disabled>Select department</option>
                  <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Area of Specialization</label>
                <select v-model="newSupervisor.specializations" multiple class="w-full border rounded px-3 py-2" required>
                  <option v-for="spec in specializations" :key="spec" :value="spec">{{ spec }}</option>
                </select>
                <small class="text-xs text-gray-500">Hold Ctrl (Cmd on Mac) to select multiple</small>
              </div>
              <div class="flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">{{ editingSupervisor ? 'Update' : 'Add' }}</button>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area of Specialization</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
            <tr v-for="sup in supervisors" :key="sup.name + sup.department">
              <td class="px-6 py-4 whitespace-nowrap">{{ sup.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ sup.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-for="(spec, i) in sup.specializations" :key="spec" class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                  {{ spec }}<span v-if="i < sup.specializations.length - 1">,</span>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                <button class="text-blue-600 hover:text-blue-800" title="Edit" @click="editSupervisor(sup)">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                  </svg>
                </button>
                <button class="text-red-600 hover:text-red-800" title="Delete" @click="deleteSupervisor(sup.name, sup.department)">
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