<template>

    <div class="relative flex w-64 items-baseline" dusk="date-time-picker">

        <div class="">
            <transition name="text-sm">
                <div class="label" v-if="date">
                    <label for="name">{{ label ? label : placeholder }}</label>
                </div>
            </transition>

            <div class="input-icon" v-if="!inline">
                <i class="fas fa-calendar"></i>
            </div>

            <div class="date-picker-input">

                <v-date-picker
                    v-model="date"
                    color="gray"
                    :input-props="{ dusk: dusk, placeholder: placeholder ? placeholder : 'Select Date' }"
                    :is-inline="inline"
                >
                </v-date-picker>

            </div>
        </div>

        <div class="text-gray-600">
            <time-picker
                v-model="time"
            ></time-picker>
        </div>

        <div class="icon remove" @click="clear()" v-if="remove">
            <i class="fas fa-times-circle"></i>
        </div>

        </div>

    </div>

</template>

<script>

    import Dates from '@/Mixins/Dates';

    export default {

        mixins: [Dates],
        props: [
            'inline', 
            'value',
            'dusk',
            'remove',
            'placeholder',
            'label',
        ],

        components: {
            'time-picker': () => import(/* webpackChunkName: "time-picker" */ '@/Components/TimePicker'),
        },

        data() {
            return {
                date: null,
                time: '',
            }
        },

        computed: {
            input() {
                return this.formatDateForInput(this.date) + ' ' + this.time + ':00';
            }
        },

        watch: {
            value() {
                this.setInput();
            },
            input() {
                this.emitInput();
            }
        },

        mounted() {
            if (this.value) {
                this.setInput();
            }
        },

        methods: {
            setInput: function() {
                if (this.value) {
                    if (!this.date) {
                        this.date = this.formatDateForDateRange(this.value);
                        this.time = this.formatTime(this.value);
                    } else {
                        if (this.formatDateForInput(this.date) != this.value) {
                            this.date = this.formatDateForDateRange(this.value);
                        }
                    }
                } else {
                    this.date = null;
                }
            },
            emitInput: function() {
                if (this.date) {
                    this.$emit('input', this.input);
                } else {
                    this.$emit('input', null);
                }
            },
            clear: function() {
                this.date = null;
                this.time = null;
            },
        },

    }
</script>
