<template>

    <transition-group name="form-error" tag="div" class="text-sm text-primary">
        <div class="pt-1" :key="message[name]" v-for="message in messages">{{ message[name] }}</div>
    </transition-group>

</template>

<script>
    export default {

        props: ['errors', 'name'],

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
