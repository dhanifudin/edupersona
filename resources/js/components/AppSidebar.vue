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
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Brain, ClipboardList, Folder, GraduationCap, LayoutGrid, MessageSquare } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';
import { index as studentDashboard } from '@/actions/App/Http/Controllers/Student/DashboardController';
import { index as questionnaireIndex } from '@/actions/App/Http/Controllers/Student/QuestionnaireController';
import { show as learningProfileShow } from '@/actions/App/Http/Controllers/Student/LearningProfileController';
import { index as materialsIndex } from '@/actions/App/Http/Controllers/Student/MaterialController';
import { index as feedbackIndex } from '@/actions/App/Http/Controllers/Student/FeedbackController';
import { index as subjectsIndex } from '@/actions/App/Http/Controllers/Student/SubjectEnrollmentController';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (user.value?.role === 'student') {
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
                href: materialsIndex().url,
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
    }

    return items;
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
                        <Link :href="dashboard()">
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
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
