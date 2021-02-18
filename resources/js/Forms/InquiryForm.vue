<template>

    <div class="flex relative">

        <div class="flex-1">
        </div>

        <div class="flex-2 relative">

            <div class="bg-white mx-8 shadow">
                <div class="flex justify-center relative py-8">
                    <div class="text-block relative">

                        <div class="" :class="isLocked('header') ? 'locked relative' : ''">
                            <input :class="[first ? 'h1' : 'h2', isLocked('header') ? 'locked' : '']" 
                                class="outline-none"
                                @focus="whisperEditing('header')" 
                                @blur="whisperEditingComplete('header')"
                                type="text" 
                                v-model="content.header" 
                                placeholder="Header" 
                                :disabled="isLocked('header')"
                            />
                        </div>

                        <editor v-model="content.body" 
                                :isLocked="isLocked('body')"
                                placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                                @focus="whisperEditing('body')"
                                @blur="whisperEditingComplete('body')"
                                @save="saveContent()"
                        ></editor>

                        <div v-if="first" class="h-1 w-16 bg-gray-400 my-4"></div>

                        <form-tags v-model="content.tags" placeholder="Livestream Tags Filter"></form-tags>

                        <div class="">
                            <div class=""><checkbox-input v-model="content.show_student_info" label="Show Student Info"></checkbox-input> </div>
                            <div class=""><checkbox-input v-model="content.show_interests" label="Show Interests"></checkbox-input> </div>
                            <div class=""><checkbox-input v-model="content.show_livestreams" label="Show Livestreams"></checkbox-input> </div>
                            <div class=""><checkbox-input v-model="content.show_livestreams_first" label="Show Livestreams First"></checkbox-input> </div>
                        </div>

                    </div>
                </div>

                <div class="pb-16">
                    <inquiry
                        :show-student-info="content.show_student_info"
                        :show-interests="content.show_interests"
                        :show-livestreams="content.show_livestreams"
                        :show-livestreams-first="content.show_livestreams_first"
                    ></inquiry>
                </div>
            </div>

        </div>

    </div>

</template>

<script>

    import Photos from '@/Mixins/Photos';
    import Feedback from '@/Mixins/Feedback';
    import SaveContent from '@/Mixins/SaveContent';
    import Whisper from '@/Mixins/Whisper';

    export default {

        props: [ 'content', 'uuid', 'first', 'contentElementIndex'],
        mixins: [ Feedback, SaveContent, Whisper ],

        components: {
            'editor': () => import(/* webpackChunkName: "editor" */ '@/Components/Editor.vue'),
            'form-tags': () => import(/* webpackChunkName: "form-tags" */ '@/Forms/Tags.vue'),
            'inquiry': () => import(/* webpackChunkName: "inquiry" */ '@/Forms/Inquiry.vue'),
            'checkbox-input': () => import(/* webpackChunkName: "checkbox-input" */ '@/Components/CheckboxInput.vue'),
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
        },

    }
</script>
