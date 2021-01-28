<template>

    <div class="relative flex items-end" dusk="date-time-picker">

        <div class="flex-1 relative">

            <v-date-picker
                v-model="dateTime"
                mode="dateTime"
                :masks="masks"
            >
                <template v-slot="{ inputValue, inputEvents }" v-if="popup">

                    <div class="flex items-center">
                        <div class="input-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <input
                            class="icon"
                            :value="inputValue"
                            v-on="inputEvents"
                            :placeholder="placeholder"
                        />
                        <div class="button text-sm" @click="dateTime = $moment.utc().toDate()" v-if="!hideNow">Now</div>
                    </div>
                </template>
            </v-date-picker>

        </div>

        <div class="icon remove" @click="clear()" v-if="remove">
            <i class="fas fa-times-circle"></i>
        </div>

    </div>

</template>

<script>

    import Dates from '@/Mixins/Dates';

    export default {
        mixins: [Dates],
        props: [
            'popup', 
            'value',
            'dusk',
            'remove',
            'placeholder',
            'label',
            'hideNow',
        ],
        data() {
            return {
                dateTime: null,
                masks: {
                    input: 'YYYY-MM-DD h:mm A',
                },
            }
        },
        watch: {
            value() {
                this.setDateTime();
            },
            dateTime() {
                if (this.dateTime) {
                    this.$emit('input', this.dateTime.toISOString());
                }
            }
        },
        mounted() {
            if (this.value) {
                this.setDateTime();
            }
        },
        methods: {
            setDateTime: function() {
                if (this.dateTime !== this.value) {
                    this.dateTime = this.$moment.utc(this.value, true).toDate();
                }
            },
            clear: function() {
                this.dateTime = null;
            },
        },
    }
</script>
