<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { generate } from '@/actions/App/Http/Controllers/Admin/ReportController';
import { BarChart3, Download, FileSpreadsheet, FileText } from 'lucide-vue-next';
import { ref } from 'vue';

interface ReportType {
    id: string;
    name: string;
    description: string;
}

interface Props {
    reportTypes: ReportType[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Admin', href: '/admin/dashboard' },
    { title: 'Laporan', href: '/admin/reports' },
];

const selectedType = ref('');
const selectedFormat = ref('');
const dateFrom = ref('');
const dateTo = ref('');
const isGenerating = ref(false);

const generateReport = () => {
    if (!selectedType.value || !selectedFormat.value) return;

    isGenerating.value = true;

    const formData = new FormData();
    formData.append('type', selectedType.value);
    formData.append('format', selectedFormat.value);
    if (dateFrom.value) formData.append('date_from', dateFrom.value);
    if (dateTo.value) formData.append('date_to', dateTo.value);

    // Use window.location for file download
    const params = new URLSearchParams();
    params.append('type', selectedType.value);
    params.append('format', selectedFormat.value);
    if (dateFrom.value) params.append('date_from', dateFrom.value);
    if (dateTo.value) params.append('date_to', dateTo.value);

    // Create a form and submit it for file download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = generate().url;
    form.style.display = 'none';

    // Add CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    // Add form fields
    const typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = selectedType.value;
    form.appendChild(typeInput);

    const formatInput = document.createElement('input');
    formatInput.type = 'hidden';
    formatInput.name = 'format';
    formatInput.value = selectedFormat.value;
    form.appendChild(formatInput);

    if (dateFrom.value) {
        const dateFromInput = document.createElement('input');
        dateFromInput.type = 'hidden';
        dateFromInput.name = 'date_from';
        dateFromInput.value = dateFrom.value;
        form.appendChild(dateFromInput);
    }

    if (dateTo.value) {
        const dateToInput = document.createElement('input');
        dateToInput.type = 'hidden';
        dateToInput.name = 'date_to';
        dateToInput.value = dateTo.value;
        form.appendChild(dateToInput);
    }

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

    setTimeout(() => {
        isGenerating.value = false;
    }, 2000);
};

const getReportIcon = (type: string) => {
    switch (type) {
        case 'learning_styles':
            return BarChart3;
        case 'student_progress':
            return FileText;
        case 'material_usage':
            return FileSpreadsheet;
        case 'class_analytics':
            return BarChart3;
        default:
            return FileText;
    }
};
</script>

<template>
    <Head title="Laporan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4 md:p-6">
            <!-- Header -->
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold tracking-tight">Laporan</h1>
                <p class="text-muted-foreground">
                    Buat dan unduh laporan dalam format PDF atau CSV
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Report Generator -->
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-2">
                            <Download class="h-5 w-5" />
                            <CardTitle>Buat Laporan</CardTitle>
                        </div>
                        <CardDescription>
                            Pilih jenis laporan dan format yang diinginkan
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Jenis Laporan</Label>
                                    <Select v-model="selectedType">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih jenis laporan" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="report in reportTypes" :key="report.id" :value="report.id">
                                                {{ report.name }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="space-y-2">
                                    <Label>Format</Label>
                                    <Select v-model="selectedFormat">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Pilih format" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="pdf">PDF</SelectItem>
                                            <SelectItem value="csv">CSV (Excel)</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label>Dari Tanggal (Opsional)</Label>
                                    <Input type="date" v-model="dateFrom" />
                                </div>
                                <div class="space-y-2">
                                    <Label>Sampai Tanggal (Opsional)</Label>
                                    <Input type="date" v-model="dateTo" />
                                </div>
                            </div>

                            <Button
                                @click="generateReport"
                                :disabled="!selectedType || !selectedFormat || isGenerating"
                                class="w-full md:w-auto"
                            >
                                <Download class="mr-2 h-4 w-4" />
                                {{ isGenerating ? 'Membuat Laporan...' : 'Unduh Laporan' }}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Report Types Info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Jenis Laporan Tersedia</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="report in reportTypes"
                                :key="report.id"
                                class="flex items-start gap-3 rounded-lg border p-3"
                                :class="{ 'border-primary bg-primary/5': selectedType === report.id }"
                            >
                                <component
                                    :is="getReportIcon(report.id)"
                                    class="h-5 w-5 mt-0.5 text-muted-foreground"
                                />
                                <div>
                                    <p class="font-medium text-sm">{{ report.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ report.description }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Quick Export Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card
                    v-for="report in reportTypes"
                    :key="report.id"
                    class="cursor-pointer hover:border-primary transition-colors"
                    @click="selectedType = report.id; selectedFormat = 'pdf'"
                >
                    <CardContent class="flex items-center gap-4 p-4">
                        <div class="rounded-full bg-primary/10 p-3">
                            <component :is="getReportIcon(report.id)" class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <p class="font-medium">{{ report.name }}</p>
                            <p class="text-xs text-muted-foreground">Klik untuk memilih</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
