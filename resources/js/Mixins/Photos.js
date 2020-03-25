export default {
    data() {
        return {
            uploads: [],
        }
    },

    computed: {
        photos() {
            return this.content.photos;
        },
        photo() {
            return this.content.photos[0];
        },
        fileUploadName() {
            return 'photos-' + this.uuid + '-' + this.content.id;
        },
    },

    watch: {

        uploads() {
            this.updatePhotos();
        },

    },

    methods: {
        
        updatePhotos: function() {
            this.$lodash.forEach(this.uploads, (upload, index) => {
                this.addUpload(upload);
            });
        },

        addUpload: function(upload) {

            if (upload.id >= 1) {

                if (!this.$lodash.find(this.photos, function(u) {
                    return u.file_upload.id == upload.id;
                })) {

                    let newPhoto = {
                        id: '0.' + this.photos.length,
                        name: '',
                        description: '',
                        alt: '',
                        sort_order: this.photos.length + 1,
                        span: 1,
                        offsetX: 50,
                        offsetY: 50,
                        fill: true,
                        large: upload.large,
                        file_upload: upload,
                    }

                    this.photos.push(newPhoto);
                }
            }
        },

        removePhoto: function(photo, index) {

            var answer = confirm('Are you sure you want to delete this photo?');
            if (answer == true) {

                this.$http.post('/photos/' + photo.id + '/remove').then( response => {

                    this.processSuccess(response);

                    let uploadIndex = this.$lodash.findIndex(this.uploads, {'id': photo.file_upload.id});
                    this.uploads.splice(uploadIndex, 1);
                    this.photos.splice(index, 1);

                }, function (error) {
                    this.processErrors(error.response);
                });

            }

        },
    },
}
