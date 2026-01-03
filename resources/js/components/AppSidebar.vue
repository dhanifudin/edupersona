<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Brain,
    ClipboardList,
    FileText,
    GraduationCap,
    LayoutGrid,
    MessageSquare,
    School,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

// Student imports
import { index as studentDashboard } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { index as questionnaireIndex } from '@/actions/App/Http/Controllers/Student/QuestionnaireController';
import { show as learningProfileShow } from '@/actions/App/Http/Controllers/Student/LearningProfileController';
import { index as studentMaterialsIndex } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { index as feedbackIndex } from '@/actions/App/Http/Controllers/Student/FeedbackController';
import { index as subjectsIndex } from '@/actions/App/Http/Controllers/Student/SubjectEnrollmentController';

// Teacher imports
import { index as teacherDashboard } from '@/actions/App/Http/Controllers/Teacher/DashboardController';
import { index as teacherMaterialsIndex } from '@/actions/App/Http/Controllers/Teacher/MaterialController';
import { index as teacherStudentsIndex } from '@/actions/App/Http/Controllers/Teacher/StudentController';

// Admin imports
import { index as adminDashboard } from '@/actions/App/Http/Controllers/Admin/DashboardController';
import { index as adminUsersIndex } from '@/actions/App/Http/Controllers/Admin/UserController';
import { index as adminClassesIndex } from '@/actions/App/Http/Controllers/Admin/ClassController';
import { index as adminSubjectsIndex } from '@/actions/App/Http/Controllers/Admin/SubjectController';
import { index as adminReportsIndex } from '@/actions/App/Http/Controllers/Admin/ReportController';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const dashboardUrl = computed(() => {
    switch (user.value?.role) {
        case 'student':
            return studentDashboard().url;
        case 'teacher':
            return teacherDashboard().url;
        case 'admin':
            return adminDashboard().url;
        default:
            return '/dashboard';
    }
});

const mainNavItems = computed<NavItem[]>(() => {
    switch (user.value?.role) {
        case 'student':
            return [
                {
                    title: 'Dashboard',
                    href: studentDashboard().url,
                    icon: LayoutGrid,
                },
                {
                    title: 'Mata Pelajaran',
                    href: subjectsIndex().url,
                    icon: GraduationCap,
                },
                {
                    title: 'Materi',
                    href: studentMaterialsIndex().url,
                    icon: BookOpen,
                },
                {
                    title: 'Kuesioner',
                    href: questionnaireIndex().url,
                    icon: ClipboardList,
                },
                {
                    title: 'Profil Belajar',
                    href: learningProfileShow().url,
                    icon: Brain,
                },
                {
                    title: 'Feedback AI',
                    href: feedbackIndex().url,
                    icon: MessageSquare,
                },
            ];

        case 'teacher':
            return [
                {
                    title: 'Dashboard',
                    href: teacherDashboard().url,
                    icon: LayoutGrid,
                },
                {
                    title: 'Materi',
                    href: teacherMaterialsIndex().url,
                    icon: BookOpen,
                },
                {
                    title: 'Siswa',
                    href: teacherStudentsIndex().url,
                    icon: Users,
                },
            ];

        case 'admin':
            return [
                {
                    title: 'Dashboard',
                    href: adminDashboard().url,
                    icon: LayoutGrid,
                },
                {
                    title: 'Pengguna',
                    href: adminUsersIndex().url,
                    icon: Users,
                },
                {
                    title: 'Kelas',
                    href: adminClassesIndex().url,
                    icon: School,
                },
                {
                    title: 'Mata Pelajaran',
                    href: adminSubjectsIndex().url,
                    icon: GraduationCap,
                },
                {
                    title: 'Laporan',
                    href: adminReportsIndex().url,
                    icon: FileText,
                },
            ];

        default:
            return [
                {
                    title: 'Dashboard',
                    href: '/dashboard',
                    icon: LayoutGrid,
                },
            ];
    }
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboardUrl">
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
            <NavFooter v-if="footerNavItems.length > 0" :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
