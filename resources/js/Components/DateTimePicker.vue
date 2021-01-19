<template>

    <div class="relative flex" dusk="date-time-picker">

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
                    v-model="datePicker"
                    color="gray"
                    :input-props="{ dusk: dusk, placeholder: placeholder ? placeholder : 'Select Date' }"
                    :is-inline="inline"
                >
                </v-date-picker>

            </div>
        </div>

        <div class="">
            <time-picker v-model="time" ></time-picker>
        </div>

        <div class="remove-icon px-1" @click="clear()" v-if="remove">
            <i class="fas fa-times"></i>
        </div>

    </div>

</template>

<script>

    export default {

        mixins: [],
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
                time: null,
                datePicker: null,
            }
        },

        computed: {
            input() {
                if (this.date && this.time) {
                    let utc = this.$moment(this.date + ' ' + this.time + ':00', 'YYYY-MM-DD HH:mm:ss').utc();
                    if (utc.isValid()) {
                        let json = utc.toJSON();
                        return json.substring(0, json.length - 1) + '000Z';
                    } else {
                        return null;
                    }
                } else {
                    return null;
                }
            }
        },

        watch: {
            value() {
                this.setInput();
            },
            input() {
                this.emitInput();
            },
            datePicker() {
                this.date = this.$moment(this.datePicker).format('YYYY-MM-DD');
            },
            date() {
                if (this.date !== this.$moment(this.datePicker).format('YYYY-MM-DD')) {
                    this.datePicker = this.$moment(this.date, 'YYYY-MM-DD').toDate();
                }
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

                    if (!this.input) {
                        this.date = this.$moment(this.value).format('YYYY-MM-DD');
                        this.time = this.$moment(this.value).format('HH:mm');
                    } else {
                        if (this.input != this.value) {
                            this.date = this.$moment(this.value).format('YYYY-MM-DD');
                            this.time = this.$moment(this.value).format('HH:mm');
                        }
                    }
                } else {
                    this.date = null;
                    this.time = null;
                }
            },

            emitInput: function() {
                if (this.input) {
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
