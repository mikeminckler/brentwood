<template>
  
    <div class="flex">
        <div class="cursor-pointer mx-4 relative flex items-center justify-center" 
            @click="toggle($event)" 
            :dusk="'checkbox-' + dusk"
        >
            <div class="absolute text-xl text-primary-600" :class="iconClass" v-if="!checked" key="unchecked"><i class="far fa-circle"></i></div>
            <div class="absolute text-xl text-primary-600" :class="iconClass" v-if="checked" key="checked"><i class="fas fa-check-circle"></i></div>
        </div>
        <div class="cursor-pointer whitespace-no-wrap" @click.stop="toggle($event)">{{ label }} <slot></slot></div>
    </div>

</template>

<script>
    export default {

        props: ['value', 'dusk', 'label', 'iconClass', 'dontStop', 'multiple'],

        data() {
            return {
                checked: false,
            }
        },

        computed: {
            index() {
                if (!this.multiple) {
                    return false;
                }

                return this.$lodash.findIndex(this.multiple, v => {
                    return v === this.value;
                });
            }
        },

        watch: {
            value() {
                if (this.multiple) {
                } else {
                    this.checked = this.value;
                }
            },
            checked() {
                if (this.multiple) {
                } else {
                    this.$emit('input', this.checked);
                }
            },
            index() {
                if (this.index >= 0) {
                    this.checked = true;
                } else {
                    this.checked = false;
                }
            }
        },

        mounted() {
            if (this.multiple) {
            } else {
                if (this.value) {
                    this.checked = this.value;
                }
            }
        },

        methods: {

            toggle: function(event) {
                if (!this.dontStop) {
                    event.stopPropagation();
                }

                if (this.multiple) {

                    if (this.index < 0) {
                        this.multiple.push(this.value);
                    } else {
                        this.multiple.splice(this.index, 1);
                    }

                } else {
                    this.checked = !this.checked;
                    this.$emit('change');
                }
            }

        }

    }
</script>
