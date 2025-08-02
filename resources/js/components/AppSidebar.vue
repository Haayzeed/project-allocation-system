<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, Folder, LayoutGrid, User, FolderKanban, School, GraduationCap, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

// Determine which menu to show based on current route
const isStudentRoute = computed(() => page.url.startsWith('/students'));
const isAdminRoute = computed(() => page.url.startsWith('/admin'));
const isSupervisorRoute = computed(() => page.url.startsWith('/supervisor'));

const mainNavItems = computed(() => {
  if (isStudentRoute.value) {
    return [
      {
        title: 'Dashboard',
        href: '/students',
        icon: LayoutGrid,
      },
    //   {
    //     title: 'My Project',
    //     href: '/students/project',
    //     icon: Folder,
    //   },
    //   {
    //     title: 'My Supervisor',
    //     href: '/students/supervisor',
    //     icon: User,
    //   },
    ];
  } else if (isSupervisorRoute.value) {
    return [
      {
        title: 'Dashboard',
        href: '/supervisor',
        icon: LayoutGrid,
      },
      {
        title: 'My Students',
        href: '/supervisor/students',
        icon: GraduationCap,
      },
    ];
  } else if (isAdminRoute.value) {
    return [
      {
        title: 'Dashboard',
        href: '/admin/dashboard',
        icon: LayoutGrid,
      },
      {
        title: 'Students',
        href: '/admin/students',
        icon: GraduationCap,
      },
      {
        title: 'Departments',
        href: '/admin/departments',
        icon: School,
      },
      {
        title: 'Supervisors',
        href: '/admin/supervisors',
        icon: User,
      },
      {
        title: 'Projects',
        href: '/admin/projects',
        icon: FolderKanban,
      },
      {
        title: 'Configurations',
        href: '/admin/configs',
        icon: Settings,
      },
    ];
  } else {
    return [
      {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
      },
      {
        title: 'Students',
        href: '/admin/students',
        icon: GraduationCap,
      },
      {
        title: 'Departments',
        href: '/admin/departments',
        icon: School,
      },
      {
        title: 'Supervisors',
        href: '/admin/supervisors',
        icon: User,
      },
      {
        title: 'Projects',
        href: '/admin/projects',
        icon: FolderKanban,
      },
      {
        title: 'Configurations',
        href: '/admin/configs',
        icon: Settings,
      },
    ];
  }
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <!-- <NavFooter :items="footerNavItems" /> -->
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
