<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Admin Management</h1>
        <Link 
          :href="route('admin.admins.create')"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
        >
          Add New Admin
        </Link>
      </div>

      <!-- Success Alert -->
      <Alert
        v-if="showSuccessAlert"
        type="success"
        :message="successMessage"
        @dismiss="dismissSuccessAlert"
      />

      <!-- Error Alert -->
      <Alert
        v-if="showErrorAlert"
        type="error"
        :message="errorMessage"
        @dismiss="dismissErrorAlert"
      />

      <!-- Admins Table -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="admin in props.admins" :key="admin.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900 dark:text-white">{{ admin.name }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">{{ admin.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span v-if="admin.banned_at" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                    Banned
                  </span>
                  <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Active
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                  {{ formatDate(admin.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex gap-2">
                    <Link 
                      :href="route('admin.admins.show', { admin: admin.id })"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded"
                      title="View"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                      </svg>
                    </Link>
                    <Link 
                      :href="route('admin.admins.edit', { admin: admin.id })"
                      class="text-blue-600 hover:text-blue-800 p-1 rounded"
                      title="Edit"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487a2.1 2.1 0 1 1 2.97 2.97L7.5 19.789l-4 1 1-4 12.362-12.302ZM19 7l-2-2" />
                      </svg>
                    </Link>
                    <button 
                      v-if="!admin.banned_at && admin.id !== currentUserId"
                      @click="banAdmin(admin.id)"
                      class="text-yellow-600 hover:text-yellow-800 p-1 rounded"
                      title="Ban"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728" />
                      </svg>
                    </button>
                    <button 
                      v-if="admin.banned_at && admin.id !== currentUserId"
                      @click="unbanAdmin(admin.id)"
                      class="text-green-600 hover:text-green-800 p-1 rounded"
                      title="Unban"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                      </svg>
                    </button>
                    <button 
                      v-if="admin.id !== currentUserId"
                      @click="deleteAdmin(admin.id)"
                      class="text-red-600 hover:text-red-800 p-1 rounded"
                      title="Delete"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Alert from '@/Components/Alert.vue';

interface Admin {
  id: number;
  name: string;
  email: string;
  banned_at?: string;
  created_at: string;
}

interface Props {
  admins: Admin[];
  flash?: {
    success?: string;
    error?: string;
  };
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Admin Management', href: '/admin/admins' },
];

// Alert states
const showSuccessAlert = ref(!!props.flash?.success);
const showErrorAlert = ref(!!props.flash?.error);
const successMessage = ref(props.flash?.success || '');
const errorMessage = ref(props.flash?.error || '');

// Get current user ID from the page props
const currentUserId = computed(() => {
  return (window as any).Laravel?.user?.id || null;
});

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString();
}

function banAdmin(adminId: number) {
  if (confirm('Are you sure you want to ban this admin? This will prevent them from logging in.')) {
    router.post(route('admin.admins.ban', { admin: adminId }), {}, {
      onSuccess: () => {
        successMessage.value = 'Admin has been banned.';
        showSuccessAlert.value = true;
      },
      onError: (errors) => {
        errorMessage.value = 'Failed to ban admin. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
}

function unbanAdmin(adminId: number) {
  if (confirm('Are you sure you want to unban this admin?')) {
    router.post(route('admin.admins.unban', { admin: adminId }), {}, {
      onSuccess: () => {
        successMessage.value = 'Admin has been unbanned.';
        showSuccessAlert.value = true;
      },
      onError: (errors) => {
        errorMessage.value = 'Failed to unban admin. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
}

function deleteAdmin(adminId: number) {
  if (confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
    router.delete(route('admin.admins.destroy', { admin: adminId }), {
      onSuccess: () => {
        successMessage.value = 'Admin deleted successfully.';
        showSuccessAlert.value = true;
      },
      onError: (errors) => {
        errorMessage.value = 'Failed to delete admin. Please try again.';
        showErrorAlert.value = true;
      }
    });
  }
}

function dismissSuccessAlert() {
  showSuccessAlert.value = false;
}

function dismissErrorAlert() {
  showErrorAlert.value = false;
}
</script>
