<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Student Dashboard', href: '/students' },
];

// Mock data for logged-in student
const student = ref({
  name: 'John Doe',
  matricNumber: 'CSC001',
  department: 'Computer Science',
  level: '400',
  project: {
    title: 'AI Chatbot Development',
    description: 'Building an intelligent chatbot using machine learning algorithms',
    status: 'In Progress',
    submittedDate: '2024-01-15',
  },
  supervisor: {
    name: 'Dr. Jane Smith',
    email: 'jane.smith@university.edu',
    department: 'Computer Science',
    specializations: ['Artificial Intelligence', 'Machine Learning'],
  },
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ student.name }}</h1>
        <p class="text-gray-600 dark:text-gray-400">Student Dashboard</p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Student Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Student Information</h2>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Name:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ student.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Matric Number:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ student.matricNumber }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Department:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ student.department }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Level:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ student.level }}</span>
            </div>
          </div>
        </div>

        <!-- Project Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Project Details</h2>
          <div class="space-y-3">
            <div>
              <span class="text-gray-600 dark:text-gray-400">Title:</span>
              <p class="font-medium text-gray-900 dark:text-white">{{ student.project.title }}</p>
            </div>
            <div>
              <span class="text-gray-600 dark:text-gray-400">Description:</span>
              <p class="text-gray-900 dark:text-white">{{ student.project.description }}</p>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Status:</span>
              <span class="px-2 py-1 text-xs font-medium rounded-full" 
                    :class="student.project.status === 'In Progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800'">
                {{ student.project.status }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Submitted:</span>
              <span class="font-medium text-gray-900 dark:text-white">{{ student.project.submittedDate }}</span>
            </div>
          </div>
        </div>

        <!-- Supervisor Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 lg:col-span-2">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Allocated Supervisor</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Name:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ student.supervisor.name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Email:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ student.supervisor.email }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Department:</span>
                <span class="font-medium text-gray-900 dark:text-white">{{ student.supervisor.department }}</span>
              </div>
            </div>
            <div>
              <span class="text-gray-600 dark:text-gray-400">Specializations:</span>
              <div class="mt-2 flex flex-wrap gap-2">
                <span v-for="spec in student.supervisor.specializations" :key="spec" 
                      class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">
                  {{ spec }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 