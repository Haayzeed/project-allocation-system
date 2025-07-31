<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

// Define props to receive data from the controller
interface Props {
  stats: {
    total_users: number;
    total_students: number;
    total_supervisors: number;
    total_admins: number;
    total_projects: number;
    total_allocations: number;
    total_departments: number;
    allocation_success_rate: number;
    average_match_score: number | null;
  };
  projectStats: Record<string, number>;
  allocationStats: Record<string, number>;
  recentAllocations: Array<any>;
  recentProjects: Array<any>;
  recentUsers: Array<any>;
  departmentStats: Array<any>;
  supervisorWorkload: Array<any>;
  monthlyAllocations: Array<any>;
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">System Overview and Statistics</p>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 rounded-lg">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Students</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_students }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 rounded-lg">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Supervisors</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_supervisors }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 rounded-lg">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Departments</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_departments }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-yellow-100 rounded-lg">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Projects</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_projects }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 rounded-lg">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Allocations</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_allocations }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-indigo-100 rounded-lg">
              <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.allocation_success_rate }}%</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-orange-100 rounded-lg">
              <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Avg Match Score</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ props.stats.average_match_score ? props.stats.average_match_score : 'N/A' }}
              </p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-2 bg-teal-100 rounded-lg">
              <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
              <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ props.stats.total_users }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Recent Allocations -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Recent Allocations</h2>
          <div class="space-y-3">
            <div v-for="allocation in props.recentAllocations" :key="allocation.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ allocation.student?.user?.name }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ allocation.project?.title }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Supervisor: {{ allocation.supervisor?.user?.name }}</p>
              </div>
              <span class="px-2 py-1 text-xs rounded-full" :class="{
                'bg-green-100 text-green-800': allocation.status === 'approved',
                'bg-yellow-100 text-yellow-800': allocation.status === 'pending',
                'bg-red-100 text-red-800': allocation.status === 'rejected'
              }">
                {{ allocation.status }}
              </span>
            </div>
            <div v-if="props.recentAllocations.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
              No recent allocations
            </div>
          </div>
        </div>

        <!-- Recent Projects -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Recent Projects</h2>
          <div class="space-y-3">
            <div v-for="project in props.recentProjects" :key="project.id" class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div>
                <p class="font-medium text-gray-900 dark:text-white">{{ project.title }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ project.student?.user?.name }}</p>
              </div>
              <span class="px-2 py-1 text-xs rounded-full" :class="{
                'bg-green-100 text-green-800': project.status === 'approved',
                'bg-yellow-100 text-yellow-800': project.status === 'submitted',
                'bg-gray-100 text-gray-800': project.status === 'draft'
              }">
                {{ project.status }}
              </span>
            </div>
            <div v-if="props.recentProjects.length === 0" class="text-center text-gray-500 dark:text-gray-400 py-4">
              No recent projects
            </div>
          </div>
        </div>
      </div>

      <!-- Department Statistics -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-8">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Department Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="department in props.departmentStats" :key="department.id" class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
            <h3 class="font-medium text-gray-900 dark:text-white mb-2">{{ department.name }}</h3>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Students: {{ department.students_count }}</span>
              <span class="text-gray-600 dark:text-gray-400">Supervisors: {{ department.supervisors_count }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <a href="/admin/students" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
              <span class="font-medium text-gray-900 dark:text-white">Manage Students</span>
            </div>
          </a>
          <a href="/admin/supervisors" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span class="font-medium text-gray-900 dark:text-white">Manage Supervisors</span>
            </div>
          </a>
          <a href="/admin/departments" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
              <span class="font-medium text-gray-900 dark:text-white">Manage Departments</span>
            </div>
          </a>
          <a href="/admin/projects" class="block p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center">
              <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span class="font-medium text-gray-900 dark:text-white">Manage Projects</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </AppLayout>
</template> 