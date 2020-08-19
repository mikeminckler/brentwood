export default {

    computed: {
        isSaving() {
            return this.$store.state.saving.find(save => save === this.contentElement.Id) ? true : false;
        },
        pageLoading() {
            return this.$store.state.pageLoading;
        },
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
                        this.$nextTick(() => {

                            let newContent = document.getElementById('c-' + response.data.content_element.uuid);
                            if (newContent) {
                                let elementRect = newContent.getBoundingClientRect();
                                let middle = newContent.offsetTop - (elementRect.height / 3);
                                window.scrollTo(0, middle);
                            }

                        });
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
                this.$lodash.mergeWith(oldContentElement, newContentElement, this.compareValues);
            } else {
            }
        },

        compareValues: function(oldValue, newValue, field) {
        },

    },

}
