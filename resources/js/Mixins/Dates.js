export default {

    methods: {

        displayDateTime: function(date) {
            return this.$moment(date).format('YYYY-MM-DD h:mma');
        }
    }
}
