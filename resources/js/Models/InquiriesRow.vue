<template>

    <div class="ignore" @click="$emit('selected', inquiry)">
        <div class="py-1 px-2"><a @click.stop :href="inquiry.url" target="_blank" title="View Inquiry"><div class="icon"><i class="fas fa-eye"></i></div></a></div>
        <div class="cursor-pointer py-1 px-2">{{ inquiry.user.name }}</div>
        <div class="py-1 px-2">{{ inquiry.target_year }}-{{ (parseInt(inquiry.target_year) + 1) }}</div>
        <div class="py-1 px-2">Grade {{ inquiry.target_grade }}</div>
        <div class="py-1 px-2">{{ $lodash.upperFirst(inquiry.student_type) }}</div>
        <div class="py-1 px-2"><a :href="'mailto:' + inquiry.user.email">{{ inquiry.user.email }}</a></div>
        <div class="py-1 px-2 text-right"><a :href="'tel:' + inquiry.phone" v-if="inquiry.phone">{{ inquiry.phone }}</a></div>
        <div class="py-1 px-2">{{ displayDateTime(inquiry.updated_at) }}</div>
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
            inquiry() {
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
