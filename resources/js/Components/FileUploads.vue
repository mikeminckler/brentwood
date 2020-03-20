<template>
  
    <div class="" :dusk="'file-uploads-' + name">

        <input type="file"
            :name="name"
            :dusk="name"
            :id="name"
            ref="files"
            @change="uploadFiles()"
            :multiple="multiple"
            v-show="false"
            :accept="type == 'image' ? 'image/*' : ''"
        />

        <transition-group name="uploading" tag="div" class="">
            <div class="bg-gray-300 odd:bg-gray-100 mt-2 overflow-hidden rounded" 
                v-for="(file, index) in files" 
                :key="file.id" 
                v-if="!show ? (file.id >= 1 ? false : true) : true"
            >

                <div class="relative w-full flex items-center">
                    <div class="absolute bg-green-200 absolute h-full top-0"  
                         v-if="file.progress <= 100" 
                        :style="'width: ' + file.progress + '%; transition: width 1000ms linear;'"></div>
                    <div class="whitespace-no-wrap flex-1 relative z-10 px-2 py-1 font-semibold">{{ file.name }}</div>
                    <div class="whitespace-no-wrap relative z-10 px-2 py-1">{{ formatBytes(file.size) }}</div>
                    <div class="cursor-pointer p-2" :dusk="'remove-file-' + file.id" @click="removeFile(file, index)">
                        <i class="fas fa-times"></i>
                    </div>
                </div>

                <div class="w-12 h-12" v-if="file.large">
                    <img :src="'/' + file.large" class="w-full h-full object-cover" />
                </div>

            </div>
        </transition-group>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],

        props: [
            'text',
            'name',
            'value',
            'multiple',
            'folder',
            'public',
            'type',
            'show',
            'items',
        ],

        components: {
        },

        data() {
            return {
                files: [],
            }
        },

        computed: {
        },

        watch: {
            value() {
                if (this.multiple) {
                    this.files = this.value;
                } else {
                    if (this.value) {
                        if (this.$lodash.has(this.value, 'id')) {
                            if (!this.$lodash.find(this.files, {'id': this.value.id})) {
                                this.files.push(this.value);
                            }
                        } else {
                            this.files = [];
                        }
                    }
                }
            },
        },

        mounted() {
            this.$eventer.$on('add-files', name => {
                if (this.name === name) {
                    this.addFiles();
                }
            });

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('add-files');
            });
        },

        methods: {

            uploadFiles: function() {

                var vue = this;
                let filesCount = this.files.length;

                this.$lodash.forEach(this.$refs.files.files, (file, index) => {

                    let reader = new FileReader();
                    reader.readAsDataURL(file);

                    reader.onload = e => {
                        let large = e.target.result;

                        let uploadingIndex = index + filesCount;
                        let newFile = {
                            id: '0.' + index,
                            name: file.name,
                            size: file.size,
                            large: '',
                            type: this.type,
                            progress: 0,
                        };

                        this.$http.post('/file-uploads/pre-validate', newFile).then( response => {

                            this.files.push(newFile);

                            let formData = new FormData();
                            let formOptions = {
                                headers: {
                                    'Content-Type': 'multipart/form-data'
                                },
                                onUploadProgress(progressEvent) {
                                    var percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                                    vue.updateProgress(uploadingIndex, percentCompleted);
                                },
                            };

                            if (this.folder) {
                                formData.append('folder', this.folder);
                            }
                            if (this.public) {
                                formData.append('public', this.public);
                            }
                            formData.append('file', file);

                            if (this.type) {
                                formData.append('type', this.type);
                            }
                            
                            this.$http.post('/file-uploads/create', formData, formOptions).then( response => {
                                this.processSuccess(response);
                                this.$set(this.files, uploadingIndex, response.data.file_upload);
                                this.files[uploadingIndex].large = large;
                                this.updateParent();
                            }, error => {
                                this.processErrors(error.response);
                                this.files.slice(uploadingIndex, 1);
                            });

                        }, error => {
                            this.processErrors(error.response);
                            this.files.slice(uploadingIndex, 1);
                        });

                    }
                });


            },

            updateParent: function() {
                if (this.multiple) {
                    this.$emit('input', this.files);
                } else {
                    this.$emit('input', this.files[0]);
                }
            },

            updateProgress: function(uploadingIndex, percentCompleted) {
                let file = this.files[uploadingIndex];
                file.progress = percentCompleted;
            },

            addFiles: function() {
                this.$refs.files.click();
            },

            removeFile: function(file, index) {

                if (file.id >= 1) {

                    var answer = confirm('Are you sure you want to delete this file?');
                    if (answer == true) {

                        this.$http.post('/file-uploads/' + file.id + '/destroy').then( response => {
                            this.processSuccess(response);
                            this.files.splice(index, 1);
                        }, function (error) {
                            this.processErrors(error.response);
                        });

                    }

                } else {

                    this.files.splice(index, 1);

                }

            },

            formatBytes: function(bytes) {
                if (bytes < 1024) return bytes + " Bytes";
                    else if(bytes < 1048576) return(bytes / 1024).toFixed(2) + " KB";
                    else if(bytes < 1073741824) return(bytes / 1048576).toFixed(2) + " MB";
                    else return(bytes / 1073741824).toFixed(2) + " GB";
            },

        },

    }
</script>


<style>

@keyframes uploading {
    0% {
        max-height: 0px;
        opacity: 0;
    }
    100%   {
        max-height: 40px;
        opacity: 1;
    }
}

.uploading-enter-active {
    animation: uploading var(--transition-time) ease-out;
}

.uploading-leave-active {
    animation: uploading var(--transition-time) reverse;
}
</style>
