<template>

    <div class="relative transition-all duration-500 background-transparent p-0" :class="expanded ? 'md:bg-white md:p-8 shadow-lg' : ''">
        <div class="text-lg hover:text-gray-800 absolute top-0 right-0 -mt-4 z-5 bg-gray-100 shadow rounded-full p-1 cursor-pointer flex items-center justify-center" 
            @click="expanded = false"
             v-if="expanded && !$store.state.editing"
        ><i class="fas fa-times-circle"></i></div>
        <transition name="expander">
            <div class="" v-show="expanded || ($store.state.editing && !preview)">
                <slot></slot>
            </div>
        </transition>
    </div>

</template>

<script>
    export default {

        props: ['uuid', 'preview'],
        data() {
            return {
                expanded: false,
            }
        },

        computed: {
            /*
            height() {
                return this.$slots.default[0].elm.offsetHeight;
            }
            */
        },

        watch: {
        },

        mounted() {

            const expandContent = uuid => {
                this.expand(uuid);
            };
            this.$eventer.$on('toggle-expander', expandContent);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('toggle-expander', expandContent);
            });

            if (this.preview) {
                //this.expanded = true;
            }
        },

        methods: {
            expand: function(uuid) {

                if (uuid === this.uuid) {
                    this.expanded = true;
                    let content = document.getElementById('c-' + this.uuid);
                    let elementRect = content.getBoundingClientRect();
                    let middle = content.offsetTop - (elementRect.height / 3);
                    window.scrollTo(0, middle);
                } else {
                    this.expanded = false;
                }

            }
        },

    }
</script>

<style>

@keyframes expander {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 75vh;
    }
}

.expander-enter-active {
    animation: expander calc(var(--transition-time) * 2) ease-in;
}

.expander-leave-active {
    animation: expander calc(var(--transition-time) * 2) reverse;
}

</style>
