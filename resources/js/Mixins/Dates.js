export default {

    methods: {

        formatDateForDateRange: function(date) {
            return this.$moment(date, 'YYYY-MM-DD').toDate();
        },

        formatDateForInput: function(date) {
            return this.$moment(date, 'MM/DD/YYYY').format('YYYY-MM-DD');
        },

        formatDate: function(date) {
            return this.$moment(date).format('YYYY-MM-DD');
        },

        formatTime: function(date) {
            return this.$moment(date).format('HH:mm');
        },

        formatDateTime: function(date) {
            return this.$moment(date).format('YYYY-MM-DD HH:mm:ss');
        },

        displayDate: function(date) {
            return this.$moment(date, 'YYYY-MM-DD').format('ddd, MMM Do, YYYY');
        },

        displayDateTime: function(date) {
            return this.$moment(date).format('ddd, MMM Do, YYYY h:mma');
        },

        displayTimestamp: function(date) {
            return this.$moment.unix(date).format('ddd, MMM Do, YYYY h:mma');
        },

        displayTime: function(date) {
            return this.$moment(date, 'HH:mm').format('h:mma');
        },

        shortDateTime: function(date) {
            return this.$moment(date).format('YYYY-MM-DD h:mma');
        },

        shortDate: function(date) {
            return this.$moment(date).format('YYYY-MM-DD');
        },

        shortDateRange: function(dates) {
            return this.$moment(dates.start, 'YYYY-MM-DD').format('ddd, Do') + '-' + this.$moment(dates.end, 'YYYY-MM-DD').format('ddd, Do');
        },

        displayDateRange: function(dates) {
            let start_date;

            if (this.$moment(dates.start, 'YYYY-MM-DD').isSame(this.$moment(dates.end, 'YYYY-MM-DD'), 'year')) {
                start_date = this.$moment(dates.start, 'YYYY-MM-DD').format('ddd, MMM Do');
            } else {
                start_date = this.$moment(dates.start, 'YYYY-MM-DD').format('ddd, MMM Do, YYYY');
            }
            return start_date + ' to ' + this.$moment(dates.end, 'YYYY-MM-DD').format('ddd, MMM Do, YYYY');
        },

        displayDuration: function(dates) {
            let duration = this.$moment(dates.end, 'YYYY-MM-DD').diff(this.$moment(dates.start, 'YYYY-MM-DD'), 'days');
            return duration + ' night' + (duration > 1 ? 's' : '');
        },

        diff: function(dates) {
            let start;
            let end;

            if (!dates) {
                return null;
            }

            if (this.$lodash.isString(dates)) {
                start = this.$moment(dates);
                end = this.$moment();
            } else {
            
                if (dates.start && dates.end) {
                    start = this.$moment(dates.start);
                    end = this.$moment(dates.end);
                }

                if (dates.start_date && dates.end_date) {
                    start = this.$moment(dates.start_date);
                    end = this.$moment(dates.end_date);
                }
            }

            if (start.isAfter(end)) {
                let old_start = start;
                let old_end = end;
                start = old_end;
                end = old_start;
            }

            if (start && end) {

                if (end.diff(start, 'seconds') < 60) {
                    return end.diff(start, 'seconds') + ' second' + (end.diff(start, 'seconds') > 1 ? 's' : '');
                } else if (end.diff(start, 'minutes') < 60) {
                    return end.diff(start, 'minutes') + ' minute' + (end.diff(start, 'minutes') > 1 ? 's' : '');
                } else if (end.diff(start, 'hours') < 24) {
                    return end.diff(start, 'hours') + ' hour' + (end.diff(start, 'hours') > 1 ? 's' : '');
                } else if (end.diff(start, 'days') < 7) {
                    return end.diff(start, 'days') + ' day' + (end.diff(start, 'days') > 1 ? 's' : '');
                } else if (end.diff(start, 'days') < 30) {
                    return end.diff(start, 'weeks') + ' week' + (end.diff(start, 'weeks') > 1 ? 's' : '');
                } else if (end.diff(start, 'months') < 12) {
                    return end.diff(start, 'months') + ' month' + (end.diff(start, 'months') > 1 ? 's' : '');
                } else {
                    return end.diff(start, 'years') + ' year' + (end.diff(start, 'years') > 1 ? 's' : '');
                }

            }
        }

    }

}
