<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

interface Props {
  project: {
    id: number;
    title: string;
    description: string;
    objectives?: string;
    methodology?: string;
    status: string;
    created_at: string;
    student: {
      id: number;
      user: {
        id: number;
        name: string;
        email: string;
      };
      student_id: string;
      level: string;
      session: string;
      department: {
        id: number;
        name: string;
      };
    };
    specializations: Array<{
      id: number;
      name: string;
    }>;
    allocation?: {
      id: number;
      status: string;
      match_score?: number;
      admin_notes?: string;
      supervisor: {
        id: number;
        user: {
          id: number;
          name: string;
          email: string;
        };
      };
    } | null;
  };
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Projects', href: '/admin/projects' },
  { title: 'View Project', href: '#' },
];

function getStatusBadgeColor(status: string): string {
  switch (status.toLowerCase()) {
    case 'submitted': return 'bg-blue-100 text-blue-800';
    case 'approved': return 'bg-green-100 text-green-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    case 'draft': return 'bg-gray-100 text-gray-800';
    default: return 'bg-yellow-100 text-yellow-800';
  }
}

function getAllocationStatusBadgeColor(status: string): string {
  switch (status.toLowerCase()) {
    case 'approved': return 'bg-green-100 text-green-800';
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function formatMatchScore(score?: number): string {
  return score ? `${Math.round(score)}%` : 'N/A';
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Header -->
      <div class="flex justify-between items-start mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.title }}</h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">Project Details</p>
        </div>
        <Link 
          :href="route('admin.projects.index')"
          class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          ← Back to Projects
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Project Information -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Project Information</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <p class="text-gray-900 dark:text-white">{{ project.title }}</p>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ project.description }}</p>
              </div>

              <div v-if="project.objectives">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Objectives</label>
                <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ project.objectives }}</p>
              </div>

              <div v-if="project.methodology">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Methodology</label>
                <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ project.methodology }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeColor(project.status)">
                  {{ project.status }}
                </span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Submitted Date</label>
                <p class="text-gray-900 dark:text-white">{{ formatDate(project.created_at) }}</p>
              </div>
            </div>
          </div>

          <!-- Specializations -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Project Specializations</h2>
            <div v-if="project.specializations.length > 0" class="flex flex-wrap gap-2">
              <span 
                v-for="spec in project.specializations" 
                :key="spec.id" 
                class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800"
              >
                {{ spec.name }}
              </span>
            </div>
            <p v-else class="text-gray-500 dark:text-gray-400">No specializations specified</p>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Student Information -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Student Information</h2>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.user.name }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.user.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Student ID</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.student_id }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Level</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.level }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Session</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.session }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                <p class="text-gray-900 dark:text-white">{{ project.student.department.name }}</p>
              </div>
            </div>
          </div>

          <!-- Allocation Information -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Allocation Status</h2>
            <div v-if="project.allocation" class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getAllocationStatusBadgeColor(project.allocation.status)">
                  {{ project.allocation.status }}
                </span>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supervisor</label>
                <p class="text-gray-900 dark:text-white">{{ project.allocation.supervisor.user.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ project.allocation.supervisor.user.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Match Score</label>
                <p class="text-gray-900 dark:text-white">{{ formatMatchScore(project.allocation.match_score) }}</p>
              </div>
              <div v-if="project.allocation.admin_notes">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes</label>
                <p class="text-gray-900 dark:text-white text-sm">{{ project.allocation.admin_notes }}</p>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-gray-500 dark:text-gray-400">No allocation assigned</p>
              <Link 
                :href="route('admin.projects.index')"
                class="mt-2 inline-block bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm"
              >
                Allocate Supervisor
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
