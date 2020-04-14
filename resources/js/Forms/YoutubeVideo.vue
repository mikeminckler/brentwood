<template>

    <div class="relative">
        <div class="relative" :class="content.full_width ? '' : 'flex'">

            <div class="relative flex-2 flex justify-center items-center relative">

                <div class="flex absolute bottom-0 w-full items-center leading-none justify-center font-oswald text-xl md:text-2xl text-gray-700 z-5 mb-1">
                    <div class="flex items-center py-2 justify-center mb-4" 
                         style="background-color: rgba(255,255,255,0.8)"
                         v-if="$store.state.editing && !content.full_width && !content.header"
                    >
                        <input class="overflow-visible p-2 -my-2 text-center font-thin" type="text" v-model="content.title" placeholder="Title" />
                    </div>
                </div>

                <div v-if="photo" class="z-5 absolute remove-icon right-0 bottom-0 mb-12" @click.stop="removePhoto(photo, 0)"><i class="fas fa-times"></i></div>

                <youtube-player :photo="photo" :content="content" :uuid="uuid"></youtube-player>
            </div>

            <div class="relative" 
                style="transition: flex calc(var(--transition-time) * 5)" 
                :class="content.full_width ? 'flex justify-center z-4 -mt-32' : 'flex-1'"
                v-show="addText || content.header || content.body || !content.full_width"
            >

                <div class="text-block" :class="content.full_width ? 'bg-white max-w-2xl py-8' : ''">

                    <div class="">
                        <input :class="first ? 'h1' : 'h2'" type="text" v-model="content.header" placeholder="Header" />
                    </div>

                    <editor v-model="content.body" 
                        placeholder="Lorem ipsum dolor sit amet, consectetur adipiscing elit."
                    ></editor>

                    <div v-if="first" class="h-1 w-16 bg-gray-400 my-4"></div>

                </div>

            </div>

        </div>

        <div class="flex items-center justify-end relative bg-gray-200 p-2 shadow mt-4 z-2">

            <div class="form pl-2">
                <input type="text" v-model="videoId" placeholder="YouTube Video ID" />
            </div>

            <checkbox-input v-model="content.full_width" label="Full Width"></checkbox-input> 

            <div class="button ml-4" @click="addText = !addText">Add Text</div>

            <div class="flex items-center px-2" v-if="!photo && videoId">
                <div class="button" @click="$eventer.$emit('add-files', fileUploadName)">
                    <div class="">Upload Banner Image</div>
                </div>
            </div>

            <div>
                <file-uploads
                    :name="fileUploadName"
                    v-model="uploads"
                    :multiple="multiplePhotos"
                    :items="photos"
                    type="image"
                ></file-uploads>
            </div>

        </div>
    </div>


</template>

<script>

    import Editor from '@/Components/Editor.vue';
    import Feedback from '@/Mixins/Feedback';
    import FileUploads from '@/Components/FileUploads';
    import CheckboxInput from '@/Components/CheckboxInput';
    import Photos from '@/Mixins/Photos';

    export default {

        props: ['content', 'uuid', 'first'],
        mixins: [Feedback, Photos],

        components: {
            'file-uploads': FileUploads,
            'checkbox-input': CheckboxInput,
            'editor': Editor,
        },

        data() {
            return {
                videoId: '',
                multiplePhotos: false,
                addText: false,
                setVideoId: _.debounce( function() {
                    this.content.video_id = this.videoId;
                }, 1000),
            }
        },

        computed: {
            contentVideoId() {
                return this.content.video_id;
            }
        },

        watch: {
            contentVideoId() {
                this.videoId = this.contentVideoId;
            },

            videoId() {
                if (this.contentVideoId !== this.videoId) {
                    this.setVideoId();
                }
            }
        },

        mounted() {
            if (this.content.video_id) {
                this.videoId = this.content.video_id;
            }
        },
    }
</script>
