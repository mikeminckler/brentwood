<template>

    <div class="ignore" @click="$emit('selected', livestream)">
        <div class="cursor-pointer py-1 px-2">{{ livestream.name }}</div>
    </div>

</template>

<script>

    import Dates from '@/Mixins/Dates.js';

    export default {

        mixins: [Dates],

        props: ['item'],

        data() {
            return {
            
            }
        },

        computed: {
            livestream() {
                return this.item;
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {
        },

    }
</script>
