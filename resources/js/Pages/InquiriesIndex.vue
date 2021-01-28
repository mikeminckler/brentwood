<template>

    <div class="mt-8">

        <div class="" v-if="$store.state.page.id < 1">

            <h1>Inquiries</h1>

            <div class="mt-4">
                <paginator resource="inquiries" @selected="viewInquiry($event)"></paginator>
            </div>

        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'paginator': () => import(/* webpackChunkName: "paginator" */ '@/Components/Paginator.vue'),
        },

        data() {
            return {
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            viewInquiry: function(inquiry) {
                console.log(inquiry);
            }
        },

    }
</script>
