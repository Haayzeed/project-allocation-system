<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Supervisor Dashboard', href: '/supervisor' },
];

// Mock data for logged-in supervisor
const supervisor = ref({
  name: 'Dr. Jane Smith',
  email: 'jane.smith@university.edu',
  department: 'Computer Science',
  specializations: ['Artificial Intelligence', 'Machine Learning'],
});

// Mock data for allocated students
const allocatedStudents = ref([
  {
    name: 'John Doe',
    matricNumber: 'CSC001',
    email: 'john@example.com',
    level: '400',
    project: {
      title: 'AI Chatbot Development',
      description: 'Building an intelligent chatbot using machine learning algorithms',
      status: 'In Progress',
      submittedDate: '2024-01-15',
    },
  },
  {
    name: 'Alice Johnson',
    matricNumber: 'CSC002',
    email: 'alice@example.com',
    level: '400',
    project: {
      title: 'Data Mining System',
      description: 'Developing a system for mining large datasets',
      status: 'Completed',
      submittedDate: '2024-01-10',
    },
  },
]);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ supervisor.name }}</h1>
        <p class="text-gray-600 dark:text-gray-400">Supervisor Dashboard</p>
      </div>

      <!-- Supervisor Information -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Supervisor Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Name:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ supervisor.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Email:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ supervisor.email }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Department:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ supervisor.department }}</span>
            </div>
          </div>
          <div>
            <span class="text-gray-600 dark:text-gray-400">Specializations:</span>
            <div class="mt-2 flex flex-wrap gap-2">
              <span v-for="spec in supervisor.specializations" :key="spec" 
                    class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                {{ spec }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Allocated Students -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Allocated Students ({{ allocatedStudents.length }})</h2>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 dark:bg-gray-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
              <tr v-for="student in allocatedStudents" :key="student.matricNumber">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Matric: {{ student.matricNumber }} | Level: {{ student.level }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.project.title }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.project.description }}</div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" 
                        :class="student.project.status === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'">
                    {{ student.project.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ student.project.submittedDate }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 