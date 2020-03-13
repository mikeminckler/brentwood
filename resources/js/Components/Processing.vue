<template>
  
    <transition name="processing">
        <div class="fixed rounded-r bottom-0 mb-20 flex items-center justify-center bg-primary-500 text-white font-semibold px-4 py-2 z-40" v-if="processing">
            <div class="flex">
                {{ text ? text : 'Processing' }}
                <div class="icon spin"><i class="fas fa-spinner"></i></div>
            </div>
        </div>
    </transition>

</template>

<script>
    export default {

        computed: {
            processing() {
                return this.$store.state.processing.active;
            },
            text() {
                return this.$store.state.processing.text;
            }
        }

    }
</script>

<style>

@keyframes processing {
    0% {
        max-height: 0px;
        transform: translateX(-100%);
        opacity: 0;
    }
    100%   {
        max-height: 100px;
        transform: translateX(0%);
        opacity: 1;
    }
}

.processing-enter-active {
    animation: processing var(--transition-time) ease-out;
}

.processing-leave-active {
    animation: processing var(--transition-time) reverse;
}

</style>
