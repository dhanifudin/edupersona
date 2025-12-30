<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { computed } from 'vue'

const props = withDefaults(defineProps<{
    modelValue?: number
    max?: number
    class?: HTMLAttributes['class']
}>(), {
    modelValue: 0,
    max: 100,
})

const percentage = computed(() => {
    return Math.min(Math.max((props.modelValue / props.max) * 100, 0), 100)
})
</script>

<template>
    <div
        role="progressbar"
        :aria-valuenow="modelValue"
        :aria-valuemin="0"
        :aria-valuemax="max"
        :class="cn(
            'relative h-2 w-full overflow-hidden rounded-full bg-primary/20',
            props.class,
        )"
    >
        <div
            class="h-full bg-primary transition-all duration-300 ease-in-out"
            :style="{ width: `${percentage}%` }"
        />
    </div>
</template>
