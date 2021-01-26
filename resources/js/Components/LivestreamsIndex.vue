<template>

    <div class="mt-8">

        <div class="" v-if="$store.state.page.id < 1">

            <h1>Livestreams</h1>

            <div class="grid grid-livestreams mt-2 py-2">

                <div class="flex link" @click="createLivestream()">
                    <div class="icon"><i class="fas fa-plus"></i></div>
                    <div class="ml-2">Create Livestream</div>
                </div>

            </div>

            <div class="">
                <paginator resource="livestreams" @selected="editLivestream($event)"></paginator>
            </div>
        </div>

        <modal v-if="showForm">
            <form-livestream :livestream="selectedLivestream"></form-livestream>
        </modal>

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
                showForm: false,
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

            saveLivestream: function() {
                
                let url = this.selectedLivestream.id >= 1 ? this.selectedLivestream.id : 'create';

                this.$http.post('/livestreams/' + url, this.selectedLivestream).then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });
            },
        },

    }
</script>
