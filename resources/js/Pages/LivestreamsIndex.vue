<template>

    <div class="md:mt-8 mx-1 md:mx-12">

        <div class="" v-if="$store.state.page.id < 1">

            <h1>Livestreams</h1>

            <div class="mt-2 md:mt-4">

                <div class="flex flex-wrap md:grid grid-livestream-row">
                    <div class="col-span-3 flex link grid-cell" @click="createLivestream()">
                        <div class="icon"><i class="fas fa-plus"></i></div>
                        <div class="ml-2">Create Livestream</div>
                    </div>
                    <div class="grid-cell hidden md:block"></div>
                    <div class="grid-cell hidden md:block">Start Date</div>
                    <div class="grid-cell hidden md:block">Youtube Page</div>
                    <div class="grid-cell hidden md:block">Popout Chat</div>
                </div>
                <paginator resource="livestreams" @selected="editLivestream($event)"></paginator>

            </div>
        </div>

        <transition name="fade">
            <modal v-if="selectedLivestream" @close="selectedLivestream = null">
                <form-livestream :livestream="selectedLivestream" @saved="refresh()"></form-livestream>
            </modal>
        </transition>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';

    export default {

        props: [],
        mixins: [Feedback],

        components: {
            'paginator': () => import(/* webpackChunkName: "paginator" */ '@/Components/Paginator.vue'),
            'modal': () => import(/* webpackChunkName: "modal" */ '@/Components/Modal.vue'),
            'form-livestream': () => import(/* webpackChunkName: "form-livestream" */ '@/Forms/Livestream.vue'),
        },

        data() {
            return {
                selectedLivestream: null,
            }
        },

        computed: {
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            createLivestream: function() {
                let livestream = {
                    id: 0,
                    name: '',
                    video_id: '',
                    start_date: '',
                    length: '',
                    tags: [],
                };

                this.selectedLivestream = livestream;
                this.showForm = true;

            },

            editLivestream: function(livestream) {
                this.selectedLivestream = livestream;
                this.showForm = true;
            },

            refresh: function() {
                console.log('REFRESH');
                this.selectedLivestream = null;
                this.$eventer.$emit('paginate', {resource: 'livestreams'});
            },

        },

    }
</script>
