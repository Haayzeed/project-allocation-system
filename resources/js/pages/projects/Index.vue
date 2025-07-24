<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Projects', href: '/projects' },
];

// Mock data for demonstration
const projects = ref([
  {
    name: 'AI Chatbot',
    student: { name: 'John Doe', email: 'john@example.com' },
    department: 'Computer Science',
    allocationStatus: 'Not Allocated',
  },
  {
    name: 'Algebraic Structures',
    student: { name: 'Jane Smith', email: 'jane@example.com' },
    department: 'Mathematics',
    allocationStatus: 'Allocated',
  },
  {
    name: 'Quantum Computing',
    student: { name: 'Alice Johnson', email: 'alice@example.com' },
    department: 'Physics',
    allocationStatus: 'Not Allocated',
  },
]);

function allocateSupervisor(project: any) {
  // Mock allocation logic
  alert(`Allocating supervisor for project: ${project.name}`);
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h1 class="text-2xl font-bold mb-4">Projects</h1>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Allocation Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
            <tr v-for="project in projects" :key="project.name + project.student.email">
              <td class="px-6 py-4 whitespace-nowrap">{{ project.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ project.student.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ project.student.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ project.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ project.allocationStatus }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button
                  class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded"
                  @click="allocateSupervisor(project)"
                  :disabled="project.allocationStatus === 'Allocated'"
                >
                  Allocate Supervisor
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template> 