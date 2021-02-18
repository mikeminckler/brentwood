<template>

    <transition name="label">
        <div class="text-sm text-gray-500 py-1 pl-4 bg-gray-200 bg-opacity-50" v-if="value">{{ label }}</div>
    </transition>

</template>

<script>
    export default {

        props: ['label', 'value'],

    }
</script>

<style>
@keyframes label {
    0% {
        opacity: 0;
        max-height: 0;
        @apply py-0;
    }
    100%   {
        opacity: 1;
        max-height: 28px;
        @apply py-1;
    }
}

.label-enter-active {
    animation: label var(--transition-time) ease-out;
}

.label-leave-active {
    animation: label var(--transition-time) reverse;
}
</style>
