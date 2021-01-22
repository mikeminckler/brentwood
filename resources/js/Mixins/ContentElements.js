export default {

    data() {
        return {
            preventChanges: false,
        }
    },

    computed: {
        isSaving() {
            return this.$store.state.saving.find( save => save === this.contentElement.id);
        },
    },
  
    methods: {

        saveContentElement: function(contentElement, add) {

            if (!contentElement) {
                contentElement = this.contentElement;
            }

            this.changedFields = [];
            //console.log('CHANGED FIELDS CLEARED');

            let savingId = contentElement.id;

            if (!this.$store.state.saving.find(save => save === contentElement.id)) {

                let url = '/content-elements/' + ( contentElement.id >= 1 ? contentElement.id : 'create');

                this.$store.dispatch('startSaving', savingId);

                console.log('SAVING CE: ' + savingId);

                this.$http.post(url, contentElement).then( response => {

                    console.log('SAVING COMPLETE CE: ' + savingId);

                    this.$store.dispatch('completeSaving', savingId);

                    if (add) {
                        this.$store.dispatch('addContentElement', response.data.content_element);
                    } else {
                        this.updateContentElement(contentElement, response.data.content_element);
                    }

                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                    this.$store.dispatch('completeSaving', savingId);
                });
            }

        },

        updateContentElement: function(oldContentElement, newContentElement) {
            this.preventChanges = true;
            console.log('PREVENT ON');
            console.log('MERGING: ' + oldContentElement.id);
            //console.log(newContentElement);
            this.$lodash.mergeWith(oldContentElement, newContentElement);
            //this.changedFields = [];

            this.$nextTick(() => {
                this.preventChanges = false;
                console.log('PREVENT OFF');
            });

        },

    },

}
