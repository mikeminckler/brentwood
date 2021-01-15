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
           let uploads;
            if (this.multiplePhotos) {
                uploads = this.uploads;
            } else {
                uploads = [this.uploads];
            }
            this.$lodash.forEach(uploads, (upload, index) => {
                this.addUpload(upload);
            });
        },

        addUpload: function(upload) {

            if (upload.id >= 1) {

                //upload.large = null;

                if (!this.$lodash.find(this.photos, function(u) {
                    return u.file_upload.id === upload.id;
                })) {

                    //console.log(upload);

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
                        stat_number: null,
                        stat_name: null,
                        link: null,
                        large: null,
                        file_upload: upload,
                        file_upload_id: upload.id,
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

                    if (this.multiplePhotos) {
                        let uploadIndex = this.$lodash.findIndex(this.uploads, {'id': photo.file_upload.id});
                        this.uploads.splice(uploadIndex, 1);
                    } else {
                        this.uploads = [];
                    }
                    this.photos.splice(index, 1);

                }, function (error) {
                    this.processErrors(error.response);
                });

            }

        },
    },
}
