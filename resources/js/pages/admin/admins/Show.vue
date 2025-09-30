<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Admin Details</h1>
          <div class="flex space-x-3">
            <Link 
              :href="route('admin.admins.edit', { admin: props.admin.id })"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
            >
              Edit Admin
            </Link>
            <Link 
              :href="route('admin.admins.index')"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded shadow"
            >
              Back to List
            </Link>
          </div>
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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Basic Information</h3>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ props.admin.name }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ props.admin.email }}</dd>
                  </div>
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd>
                      <span v-if="props.admin.banned_at" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        Banned
                      </span>
                      <span v-else class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        Active
                      </span>
                    </dd>
                  </div>
                </dl>
              </div>
              
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Account Information</h3>
                <dl class="space-y-3">
                  <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ formatDate(props.admin.created_at) }}</dd>
                  </div>
                  <div v-if="props.admin.banned_at">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Banned At</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ formatDate(props.admin.banned_at) }}</dd>
                  </div>
                </dl>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
              <div class="flex space-x-3">
                <Link 
                  :href="route('admin.admins.edit', { admin: props.admin.id })"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow"
                >
                  Edit Admin
                </Link>
                <button 
                  v-if="!props.admin.banned_at && props.admin.id !== currentUserId"
                  @click="banAdmin"
                  class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded shadow"
                >
                  Ban Admin
                </button>
                <button 
                  v-if="props.admin.banned_at && props.admin.id !== currentUserId"
                  @click="unbanAdmin"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow"
                >
                  Unban Admin
                </button>
                <button 
                  v-if="props.admin.id !== currentUserId"
                  @click="deleteAdmin"
                  class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow"
                >
                  Delete Admin
                </button>
              </div>
            </div>
          </div>
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
  admin: Admin;
  flash?: {
    success?: string;
    error?: string;
  };
}

const props = defineProps<Props>();

const breadcrumbs = [
  { title: 'Admin', href: '/admin/dashboard' },
  { title: 'Admin Management', href: '/admin/admins' },
  { title: props.admin.name, href: `/admin/admins/${props.admin.id}` },
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

function banAdmin() {
  if (confirm('Are you sure you want to ban this admin? This will prevent them from logging in.')) {
    router.post(route('admin.admins.ban', { admin: props.admin.id }), {}, {
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

function unbanAdmin() {
  if (confirm('Are you sure you want to unban this admin?')) {
    router.post(route('admin.admins.unban', { admin: props.admin.id }), {}, {
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

function deleteAdmin() {
  if (confirm('Are you sure you want to delete this admin? This action cannot be undone.')) {
    router.delete(route('admin.admins.destroy', { admin: props.admin.id }), {
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
