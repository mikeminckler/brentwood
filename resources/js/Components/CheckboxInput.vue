<template>
  
    <div class="flex">
        <div class="cursor-pointer mx-2 relative flex items-center justify-center rounded-full bg-white w-5 h-5 border border-gray-500 shadow-inner" 
            @click="toggle($event)" 
            :dusk="'checkbox-' + dusk"
        >
            <div class="absolute text-xl text-primary-600" :class="iconClass" v-if="checked" key="checked"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="cursor-pointer whitespace-no-wrap" @click.stop="toggle($event)">{{ label }} <slot></slot></div>
    </div>

</template>

<script>
    export default {

        props: ['value', 'dusk', 'label', 'iconClass', 'dontStop'],

        data() {
            return {
                checked: false,
            }
        },

        watch: {
            value() {
                this.checked = this.value;
            },
            checked() {
                this.$emit('input', this.checked);
            }
        },

        mounted() {
            if (this.value) {
                this.checked = this.value;
            }
        },

        methods: {

            toggle: function(event) {
                if (!this.dontStop) {
                    event.stopPropagation();
                }
                this.checked = !this.checked;
                this.$emit('change');
            }

        }

    }
</script>
