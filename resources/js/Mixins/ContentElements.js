export default {
  
    methods: {

        saveContentElement: function(contentElement, add) {

            if (!contentElement) {
                contentElement = this.contentElement;
            }

            let savingId = contentElement.id;

            if (!this.$store.state.saving.find(save => save === contentElement.id)) {

                let url = '/content-elements/' + ( contentElement.id >= 1 ? contentElement.id : 'create');

                this.$store.dispatch('startSaving', contentElement.id);

                this.$http.post(url, contentElement).then( response => {

                    if (add) {
                        this.$store.dispatch('addContentElement', response.data.content_element);
                        this.$store.dispatch('completeSaving', savingId);
                    } else {
                        this.$lodash.merge(contentElement, response.data.content_element);
                    }

                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                    this.$store.dispatch('completeSaving', savingId);
                });
            } else {
                this.$store.dispatch('completeSaving', savingId);
            }

        },

    },

}
