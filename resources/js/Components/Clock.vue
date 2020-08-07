<template>

    <div class="flex items-baseline">
        <div class="flex justify-center items-baseline font-bold">
            <div class="">{{ time }}</div>
            <div class="">{{ ampm }}</div>
        </div>
        <div class="ml-1">{{ today }} @Brentwood</div>
    </div>

</template>

<script>
    export default {

        props: [],
        data() {
            return {
                time: '',
                ampm: '',
                today: '',
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
            this.renderClock();
            this.today = this.$moment().tz('America/Vancouver').format('MMMM Do');

            const render = () => {
                this.renderClock();
            };

            setInterval(render, 60000);
        },

        methods: {
            renderClock: function() {
                this.time = this.$moment().tz('America/Vancouver').format('h:mm');
                this.ampm = this.$moment().tz('America/Vancouver').format('a');
            }
        },

    }
</script>
