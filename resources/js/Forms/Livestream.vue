<template>

    <div class="p-4">

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

                <div class="input">
                    <checkbox-input v-model="livestream.enable_chat" label="Enable Chat"></checkbox-input>
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

        <div class="">

            <h3>Moderators</h3>
            <div class="mt-2">
                <autocomplete
                    url="/users/search"
                    v-model="livestream.moderators"
                    name="moderator"
                    :multiple="true"
                    placeholder="Add Moderator"
                    :hideLabel="true"
                    :remove="true"
                    @remove="removeModerator($event)"
                ></autocomplete>
            </div>

            <h3>Allowed Users</h3>
            <div class="mt-2">
                <autocomplete
                    url="/roles/search"
                    v-model="livestream.roles"
                    name="role"
                    :multiple="true"
                    placeholder="Add Role"
                    :hideLabel="true"
                    :remove="true"
                    @remove="removeRole($event)"
                ></autocomplete>
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
            'autocomplete': () => import(/* webpackChunkName: "autocomplete" */ '@/Components/Autocomplete'),
            'form-label': () => import(/* webpackChunkName: "form-label" */ '@/Components/FormLabel.vue'),
            'date-time-picker': () => import(/* webpackChunkName: "date-time-picker" */ '@/Components/DateTimePicker.vue'),
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
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

            removeRole: function(role) {
                this.livestream.roles = this.$lodash.xor(this.livestream.roles, [role]);
            },

            removeModerator: function(user) {
                this.livestream.moderators = this.$lodash.xor(this.livestream.moderators, [user]);
            },

        },

    }
</script>
