<template>
  
    <transition name="fade">
        <div class="bg-gray-100 shadow-lg p-1 rounded-l fixed z-50 bottom-0 right-0 mb-20 overflow-hidden" v-show="feedback.length > 0">
            <transition-group name="feedback" mode="out-in">
                <div class="flex text-sm cursor-pointer"  
                    :key="feedback.key" 
                    :class="feedback.type" 
                    v-for="(feedback, index) in feedback"
                    @click="removeFeedback(feedback, index)"
                    :data-index="index"
                >
                    <div class="pl-2 py-1" v-if="feedback.type == 'success'"><i class="fas fa-check"></i></div>
                    <div class="pl-2 py-1" v-if="feedback.type == 'error'"><i class="fas fa-times"></i></div>
                    <div class="pl-2 py-1" v-if="feedback.type == 'info'"><i class="fas fa-info-circle"></i></div>
                    <div class="px-2 py-1">{{ feedback.message }}</div>
                </div>
            </transition-group>
        </div>
    </transition>

</template>

<script>

    export default {

        props: ['errors', 'success', 'error'],

        mixins: [],

        data() {
            return {
            }
        },

        computed: {
            feedback() {
                return this.$store.state.feedback;
            },
        },

        watch: {
        },

        mounted() {
            
            if (this.$lodash.size(this.errors) > 0) {
                this.processErrors(JSON.parse(this.errors));
            }

            if (this.error) {
                this.$store.dispatch('addFeedback', {'type': 'error', 'message': this.error});
            }

            if (this.success) {
                this.$store.dispatch('addFeedback', {'type': 'success', 'message': this.success});
            }

        },

        methods: {

            removeFeedback: function (feedback, index) {
                this.$store.state.feedback.splice(index, 1);
            },

        }

    }

</script>

<style>

    @keyframes feedback {
        0% {
            max-height: 0px;
            transform: translateX(100%);
            opacity: 0;
        }
        100%   {
            max-height: 100px;
            transform: translateX(0%);
            opacity: 1;
        }
    }

    .feedback-enter-active {
        animation: feedback var(--transition-time) ease-out;
    }

    .feedback-leave-active {
        animation: feedback var(--transition-time) reverse;
    }

</style>
