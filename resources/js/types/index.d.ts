import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    role: 'student' | 'teacher' | 'admin';
    student_id_number?: string | null;
    teacher_id_number?: string | null;
    phone?: string | null;
    learning_interests?: string[] | null;
    created_at: string;
    updated_at: string;
}

export interface ClassRoom {
    id: number;
    name: string;
    grade_level: string;
    major: string;
    academic_year: string;
}

export interface Subject {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    category?: string | null;
    is_active: boolean;
}

export interface LearningStyleProfile {
    id: number;
    user_id: number;
    visual_score: number;
    auditory_score: number;
    kinesthetic_score: number;
    dominant_style: 'visual' | 'auditory' | 'kinesthetic' | 'mixed';
    analyzed_at: string;
}

export interface LearningStyleQuestion {
    id: number;
    question_text: string;
    style_type: 'visual' | 'auditory' | 'kinesthetic';
    order: number;
}

export interface StudentDashboardData {
    hasCompletedQuestionnaire: boolean;
    learningProfile?: LearningStyleProfile;
    currentClass?: ClassRoom;
    recentActivities?: LearningActivity[];
    recommendations?: AiRecommendation[];
}

export interface LearningActivity {
    id: number;
    material_id: number;
    duration_seconds: number;
    started_at: string;
    completed_at?: string | null;
    material?: LearningMaterial;
}

export interface AiRecommendation {
    id: number;
    material_id: number;
    reason: string;
    relevance_score: number;
    is_viewed: boolean;
    viewed_at?: string | null;
    material?: LearningMaterial;
}

export interface LearningMaterial {
    id: number;
    title: string;
    description?: string;
    topic?: string;
    type: 'video' | 'document' | 'infographic' | 'audio' | 'simulation';
    learning_style: 'visual' | 'auditory' | 'kinesthetic' | 'all';
    difficulty_level: 'beginner' | 'intermediate' | 'advanced';
    content_url?: string | null;
    file_path?: string | null;
}

export interface AiFeedback {
    id: number;
    user_id: number;
    context_type: string;
    context_id?: number | null;
    feedback_text: string;
    feedback_type: string;
    is_read: boolean;
    generated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
