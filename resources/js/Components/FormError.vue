<template>

    <transition-group name="form-error" tag="div" class="text-sm text-primary bg-gray-200 bg-opacity-50">
        <div class="py-1 pl-4 border-b border-primary" :key="message[name]" v-if="!hide" v-for="message in messages">{{ message[name] }}</div>
    </transition-group>

</template>

<script>
    export default {

        props: ['errors', 'name', 'hide'],

        data() {
            return {
            
            }
        },

        computed: {
            messages() {
                return this.$lodash.filter(this.errors, error => {
                    return this.$lodash.has(error, this.name);
                });
            }
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>

<style>
@keyframes form-error {
    0% {
        opacity: 0;
        max-height: 0;
        @apply pt-0;
    }
    100%   {
        opacity: 1;
        max-height: 28px;
        @apply pt-1;
    }
}

.form-error-enter-active {
    animation: form-error var(--transition-time) ease-out;
}

.form-error-leave-active {
    animation: form-error var(--transition-time) reverse;
}
</style>
