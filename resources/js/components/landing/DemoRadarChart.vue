<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';

interface Props {
    visual: number;
    auditory: number;
    kinesthetic: number;
}

const props = withDefaults(defineProps<Props>(), {
    visual: 60,
    auditory: 40,
    kinesthetic: 50,
});

// SVG dimensions
const size = 200;
const center = size / 2;
const maxRadius = 80;
const levels = 5;

// Calculate point positions for radar chart (3 axes at 120 degrees apart)
const getPoint = (angle: number, value: number) => {
    const normalizedValue = (value / 100) * maxRadius;
    const radians = (angle - 90) * (Math.PI / 180);
    return {
        x: center + normalizedValue * Math.cos(radians),
        y: center + normalizedValue * Math.sin(radians),
    };
};

const visualPoint = computed(() => getPoint(0, props.visual));
const auditoryPoint = computed(() => getPoint(120, props.auditory));
const kinestheticPoint = computed(() => getPoint(240, props.kinesthetic));

const dataPath = computed(() => {
    return `M ${visualPoint.value.x},${visualPoint.value.y} L ${auditoryPoint.value.x},${auditoryPoint.value.y} L ${kinestheticPoint.value.x},${kinestheticPoint.value.y} Z`;
});

// Grid lines
const gridLevels = computed(() => {
    return Array.from({ length: levels }, (_, i) => {
        const radius = ((i + 1) / levels) * maxRadius;
        const v = getPoint(0, ((i + 1) / levels) * 100);
        const a = getPoint(120, ((i + 1) / levels) * 100);
        const k = getPoint(240, ((i + 1) / levels) * 100);
        return { radius, path: `M ${v.x},${v.y} L ${a.x},${a.y} L ${k.x},${k.y} Z` };
    });
});

// Axis lines
const axes = computed(() => [
    { angle: 0, label: 'Visual', color: 'text-blue-600' },
    { angle: 120, label: 'Auditori', color: 'text-green-600' },
    { angle: 240, label: 'Kinestetik', color: 'text-orange-600' },
]);

const getAxisEnd = (angle: number) => getPoint(angle, 100);

// Dominant style
const dominantStyle = computed(() => {
    const scores = [
        { type: 'visual', value: props.visual, label: 'Visual', color: 'bg-blue-500' },
        { type: 'auditory', value: props.auditory, label: 'Auditori', color: 'bg-green-500' },
        { type: 'kinesthetic', value: props.kinesthetic, label: 'Kinestetik', color: 'bg-orange-500' },
    ];
    return scores.reduce((max, curr) => (curr.value > max.value ? curr : max));
});

const getLabelPosition = (angle: number) => {
    const point = getPoint(angle, 115);
    return { x: point.x, y: point.y };
};
</script>

<template>
    <div class="flex flex-col items-center space-y-4">
        <!-- Radar Chart SVG -->
        <svg :width="size" :height="size" class="overflow-visible">
            <!-- Grid levels -->
            <path
                v-for="(level, index) in gridLevels"
                :key="index"
                :d="level.path"
                fill="none"
                stroke="currentColor"
                class="text-border"
                stroke-width="1"
            />

            <!-- Axis lines -->
            <line
                v-for="axis in axes"
                :key="axis.angle"
                :x1="center"
                :y1="center"
                :x2="getAxisEnd(axis.angle).x"
                :y2="getAxisEnd(axis.angle).y"
                stroke="currentColor"
                class="text-muted-foreground/50"
                stroke-width="1"
            />

            <!-- Data area -->
            <path :d="dataPath" fill="hsl(var(--primary) / 0.2)" stroke="hsl(var(--primary))" stroke-width="2" />

            <!-- Data points -->
            <circle :cx="visualPoint.x" :cy="visualPoint.y" r="4" class="fill-blue-500" />
            <circle :cx="auditoryPoint.x" :cy="auditoryPoint.y" r="4" class="fill-green-500" />
            <circle :cx="kinestheticPoint.x" :cy="kinestheticPoint.y" r="4" class="fill-orange-500" />

            <!-- Labels -->
            <text
                v-for="axis in axes"
                :key="'label-' + axis.angle"
                :x="getLabelPosition(axis.angle).x"
                :y="getLabelPosition(axis.angle).y"
                text-anchor="middle"
                dominant-baseline="middle"
                class="fill-current text-xs font-medium"
                :class="axis.color"
            >
                {{ axis.label }}
            </text>
        </svg>

        <!-- Scores -->
        <div class="flex flex-wrap justify-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-blue-500" />
                <span>Visual: {{ visual }}%</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-green-500" />
                <span>Auditori: {{ auditory }}%</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-orange-500" />
                <span>Kinestetik: {{ kinesthetic }}%</span>
            </div>
        </div>

        <!-- Dominant Style Badge -->
        <div class="text-center">
            <p class="mb-2 text-sm text-muted-foreground">Gaya Belajar Dominan</p>
            <Badge :class="dominantStyle.color" class="text-sm">
                {{ dominantStyle.label }}
            </Badge>
        </div>
    </div>
</template>
