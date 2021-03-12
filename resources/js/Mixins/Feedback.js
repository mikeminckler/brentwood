export default {
  
    props: [],

    data() {
        return {
            processing: false,
            formErrors: [],
        }
    },

    methods: {

        processErrors: function(response) {

            this.$store.dispatch('clearErrorsFeedback');
            //this.$eventer.$emit('processing-finished');

            if (response) {

                if (response.status == '404') {
                    this.$store.dispatch('addFeedback', {'type': 'error', 'message': response.data.message});
                } else if (response.status == '401') {
                    this.$store.dispatch('addFeedback', {'type': 'error', 'message': 'You dont have access to that page'});
                } else if (response.status == '403') {

                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    }

                    let error_message;
                    if (response.data.error) {
                        error_message = response.data.error;
                    } else {
                        error_message = 'You dont have access to that item';
                    }

                    this.$store.dispatch('addFeedback', {'type': 'error', 'message': error_message});
                } else if (response.status == '422') {

                    if (this.$lodash.has(response.data, 'error')) {
                        this.$store.dispatch('addFeedback', {'type': 'error', 'message': response.data.error});
                    }

                    this.formErrors = [];

                    this.$lodash.forEach (response.data.errors, (errors, input) => {

                        this.$lodash.forEach (errors, error => {
                            this.$store.dispatch('addFeedback', {'type': 'error', 'message': error, 'input': input});
                            let e = {};
                            e[input] = error;
                            this.formErrors.push(e);
                        });

                    });

                } else if(response.status == '419') {
                    this.$store.dispatch('addFeedback', {'type': 'error', 'message': 'It looks like your session has expired, please reload the page.'});
                    //location.reload();
                } else {
                    this.$store.dispatch('addFeedback', {'type': 'error', 'message': 'There was a problem with the server, we\'ll look into this asap!'});
                }
            }

        },

        processSuccess: function(response) {

            this.$store.dispatch('clearErrorsFeedback');
            this.formErrors = [];
            //this.$eventer.$emit('processing-finished');

            if (response.data.success) {
                this.$store.dispatch('addFeedback', {'type': 'success', 'message': response.data.success});
            }

            if (response.data.info) {
                this.$store.dispatch('addFeedback', {'type': 'info', 'message': response.data.info});
            }

            if (response.data.redirect) {
                window.location.href = response.data.redirect;
            }
        },

        startProcessing: function(text) {
            this.$store.dispatch('clearErrorsFeedback');
            this.processing = true;
            this.$store.dispatch('processing', {active: true, text: text});
        },

        stopProcessing: function() {
            this.processing = false;
            setTimeout(() => {
                this.$store.dispatch('processing', {active: false, text: ''});
            }, 1000);
        },

    },

}
