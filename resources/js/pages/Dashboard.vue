<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

// Accept stats prop from backend
defineProps<{ stats: {
    total_students: number;
    total_projects: number;
    total_departments: number;
    total_supervisors: number;
} }>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Stats Section -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4 mb-4">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 flex flex-col items-center border border-sidebar-border/70 dark:border-sidebar-border">
                    <span class="text-2xl font-bold">{{ stats.total_students }}</span>
                    <span class="text-gray-500 text-sm">Registered Students</span>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 flex flex-col items-center border border-sidebar-border/70 dark:border-sidebar-border">
                    <span class="text-2xl font-bold">{{ stats.total_projects }}</span>
                    <span class="text-gray-500 text-sm">Registered Topics</span>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 flex flex-col items-center border border-sidebar-border/70 dark:border-sidebar-border">
                    <span class="text-2xl font-bold">{{ stats.total_departments }}</span>
                    <span class="text-gray-500 text-sm">Departments</span>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow p-4 flex flex-col items-center border border-sidebar-border/70 dark:border-sidebar-border">
                    <span class="text-2xl font-bold">{{ stats.total_supervisors }}</span>
                    <span class="text-gray-500 text-sm">Supervisors</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
