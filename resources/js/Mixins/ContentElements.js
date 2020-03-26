export default {

    computed: {
        isSaving() {
            return this.$store.state.saving.find(save => save === this.contentElement.Id) ? true : false;
        }
    },
  
    methods: {

        saveContentElement: function(contentElement, add) {

            if (!contentElement) {
                contentElement = this.contentElement;
            }

            let savingId = contentElement.id;

            if (!this.$store.state.saving.find(save => save === contentElement.id)) {

                let url = '/content-elements/' + ( contentElement.id >= 1 ? contentElement.id : 'create');

                this.$store.dispatch('startSaving', contentElement.id);

                this.changed = false;

                this.$http.post(url, contentElement).then( response => {

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
            if (!this.changed) {
                this.preventWatcher = true;
                this.$lodash.merge(oldContentElement, newContentElement);
            } else {
            }
        },

    },

}
