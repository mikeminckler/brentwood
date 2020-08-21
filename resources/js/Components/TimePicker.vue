<template>

    <div class="input relative w-24" @blur="show = false">

        <transition name="text-sm">
            <div class="label" v-if="input">
                <label for="name">{{ label ? label : placeholder }}</label>
            </div>
        </transition>

        <div class="input-icon" v-if="!inline">
            <i class="fas fa-clock"></i>
        </div>

        <div class="time-picker-input">
            <input type="text" 
                :id="name" 
                :placeholder="placeholder ? placeholder : 'Time'" 
                ref="input"
                v-model="input" 
                @input="updateValue($event.target.value)"
                @keyup.enter="show = false"
                :dusk="name"
                @focus="show = true"
                autocomplete="off"
                class="py-2 px-3 leading-tight border rounded border-gray-400 bg-white outline-none"
            />
        </div>

        <transition name="time-picker-slider">
            <div class="flex p-2 absolute z-20 shadow rounded-b" v-if="show">
                <div class="">
                    <div class="label">Hour</div>
                    <div class=""><input type="range" min="0" max="23" v-model="hour" class="slider outline-none" id="time-slider" /></div>
                    <div class="label">Minutes</div>
                    <div class=""><input type="range" min="0" step="5" max="59" v-model="minute" class="slider outline-none" id="time-slider" /></div>
                </div>
                <div class="pl-1">
                    <div class="icon" @click="show = false"><i class="fas fa-times"></i></div>
                </div>
            </div>
        </transition>

    </div>

</template>

<script>

    export default {

        mixins: [],
        
        props: [
            'name',
            'type', 
            'placeholder',
            'label',
            'inline',
            'value',
        ],

        data() {
            return {
                input: '',
                show: false,
                hour: 0,
                minute: 0,
            }
        },

        computed: {
            time() {
                return this.$moment({ hour: this.hour, minute: this.minute}).format('HH:mm');
            }
        },

        watch: {

            value() {
                if (this.$moment(this.value, 'H:mm', true).isValid()) {
                    this.input = this.value;
                }
            },

            time() {
                this.updateValue(this.time);
            },

            input() {

                if (this.$moment(this.input, 'H:mm', true).isValid()) {
                    let time_array = this.input.replace(/(^:)|(:$)/g, '').split(":");
                    if (this.hour != time_array[0]) {
                        this.hour = time_array[0];
                    }
                    if (this.minute != time_array[1]) {
                        this.minute = time_array[1];
                    }
                }

            },
        },

        mounted() {
            if (this.value) {
                if (this.$moment(this.value, 'H:mm', true).isValid()) {
                    this.input = this.value;
                }
            }
        },

        methods: {

            updateValue: function (value) {
                if (this.$moment(value, 'H:mm', true).isValid()) {
                    this.input = value;
                    this.$emit('input', value);
                }
            },
        
        },

    }
</script>

<style>

@keyframes time-picker-slider {
    0% {
        opacity: 0;
        max-height: 0;
    }
    100%   {
        opacity: 1;
        max-height: 130px;
    }
}

.time-picker-slider-enter-active {
    animation: time-picker-slider var(--transition-time) ease-out;
}

.time-picker-slider-leave-active {
    animation: time-picker-slider var(--transition-time) reverse;
}

</style>
