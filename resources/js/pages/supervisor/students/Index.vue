<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

const breadcrumbs = [
  { title: 'Supervisor', href: '/supervisor' },
  { title: 'My Students', href: '/supervisor/students' },
];

// Mock data for allocated students
const students = ref([
  {
    name: 'John Doe',
    matricNumber: 'CSC001',
    email: 'john@example.com',
    level: '400',
    department: 'Computer Science',
    project: {
      title: 'AI Chatbot Development',
      description: 'Building an intelligent chatbot using machine learning algorithms',
      status: 'In Progress',
      submittedDate: '2024-01-15',
      progress: 65,
    },
    lastContact: '2024-01-20',
    nextMeeting: '2024-01-25',
  },
  {
    name: 'Alice Johnson',
    matricNumber: 'CSC002',
    email: 'alice@example.com',
    level: '400',
    department: 'Computer Science',
    project: {
      title: 'Data Mining System',
      description: 'Developing a system for mining large datasets',
      status: 'Completed',
      submittedDate: '2024-01-10',
      progress: 100,
    },
    lastContact: '2024-01-18',
    nextMeeting: '2024-01-22',
  },
  {
    name: 'Bob Wilson',
    matricNumber: 'CSC003',
    email: 'bob@example.com',
    level: '400',
    department: 'Computer Science',
    project: {
      title: 'Mobile App Development',
      description: 'Creating a cross-platform mobile application',
      status: 'In Progress',
      submittedDate: '2024-01-12',
      progress: 30,
    },
    lastContact: '2024-01-19',
    nextMeeting: '2024-01-26',
  },
]);

function getStatusColor(status: string) {
  switch (status) {
    case 'Completed':
      return 'bg-green-100 text-green-800';
    case 'In Progress':
      return 'bg-yellow-100 text-yellow-800';
    case 'Not Started':
      return 'bg-gray-100 text-gray-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

function getProgressColor(progress: number) {
  if (progress >= 80) return 'bg-green-500';
  if (progress >= 50) return 'bg-yellow-500';
  return 'bg-red-500';
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Students</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your allocated students and their projects</p>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200">
            <tr v-for="student in students" :key="student.matricNumber">
              <!-- Student Column -->
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.name }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Matric: {{ student.matricNumber }} | Level: {{ student.level }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Department: {{ student.department }}
                  </div>
                </div>
              </td>
              
              <!-- Project Column -->
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ student.project.title }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                    {{ student.project.description }}
                  </div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Submitted: {{ student.project.submittedDate }}
                  </div>
                </div>
              </td>
              
              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusColor(student.project.status)">
                  {{ student.project.status }}
                </span>
              </td>
              
              <!-- Contact Column -->
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 dark:text-white">
                  <div>Last: {{ student.lastContact }}</div>
                  <div>Next: {{ student.nextMeeting }}</div>
                </div>
              </td>
              
              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex gap-2">
                  <button class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                    View
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template> 