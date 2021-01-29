<template>

    <div class="">

        <h2>{{ livestream.id >= 1 ? 'Edit' : 'Create' }} Livestream</h2>

        <div class="form mt-4 md:flex">

            <div class="md:pr-4">

                <form-label label="Name" :value="livestream.name"></form-label>
                <div class="input">
                    <input type="text" autofocus v-model="livestream.name" placeholder="Name" />
                </div>

                <form-label label="Youtube Video ID" :value="livestream.video_id"></form-label>
                <div class="input">
                    <input type="text" v-model="livestream.video_id" placeholder="Youtube Video ID" />
                </div>

                <div class="mb-4" v-show="livestream.video_id.length">
                    <youtube-player :content="content" :uuid="livestream.id"></youtube-player>
                </div>

                <form-label label="Category" :value="livestream.tags.length > 0"></form-label>
                <div class="input">
                    <form-tags v-model="livestream.tags" placeholder="Select a Category"></form-tags>
                </div>

            </div>

            <div class="">

                <form-label label="Start Date & Time" :value="true"></form-label>
                <div class="input">
                    <date-time-picker 
                        v-model="livestream.start_date" 
                        placeholder="Start Date & Time"
                        :hide-now="true"
                        :popup="false"
                    ></date-time-picker>
                </div>

                <form-label label="Length in Minutes" :value="livestream.length"></form-label>
                <div class="input">
                    <input type="number" min="1" v-model="livestream.length" placeholder="Length in Minutes" />
                </div>

                <form-label label="End Date & Time" :value="livestream.start_date && livestream.length > 0"></form-label>
                <transition name="input">
                    <div class="input" v-if="livestream.start_date && livestream.length > 0">
                        <div class="fake-input">{{ endDate }}</div>
                    </div>
                </transition>

            </div>

        </div>

        <div class="flex">
            <div class="flex-1 button-secondary mr-2" @click="$eventer.$emit('close-modal')">Cancel</div>
            <div class="flex-1 button ml-2" @click="saveLivestream()">
                <div class="icon"><i class="fas fa-save"></i></div>
                <div class="pl-2">Save</div>
            </div>
        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback.js';
    import Dates from '@/Mixins/Dates';

    export default {

        props: ['livestream'],

        mixins: [Feedback, Dates],

        components: {
            'form-label': () => import(/* webpackChunkName: "form-label" */ '@/Components/FormLabel.vue'),
            'date-time-picker': () => import(/* webpackChunkName: "date-time-picker" */ '@/Components/DateTimePicker.vue'),
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
        },

        data() {
            return {
            
            }
        },

        computed: {
            endDate() {
                return this.displayDateTime(this.$moment(this.livestream.start_date).add(this.livestream.length, 'minutes'));
            },

            content() {
                return {
                    video_id: this.livestream.video_id,
                    photos: [],
                };
            },
        },

        watch: {
        },

        mounted() {
        },

        methods: {

            saveLivestream: function() {
                
                let url = this.livestream.id >= 1 ? this.livestream.id : 'create';

                this.$http.post('/livestreams/' + url, this.livestream).then( response => {
                    this.processSuccess(response);
                    this.$emit('saved');
                }, error => {
                    this.processErrors(error.response);
                });
            },

        },

    }
</script>