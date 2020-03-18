import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        editing: false,
        feedback: [],
        processing: {
            active: false,
            text: '',
        },
        page: {
            id: 0,
            name: '',
            parent_page_id: '',
            unlisted: 0,
            sort_order: 0,
            content_elements: [],
        },
    },

    mutations: {
        setEditing(state, editing) {
            state.editing = editing;
        },

        setPage(state, page) {
            state.page = page;
        },

        addContentElement(state, contentElement) {
            state.page.content_elements.push(contentElement);
        },

        addFeedback (state, item) {
            state.feedback.push(item);
        },

        clearFeedback (state) {
            state.feedback = state.feedback.filter( function(item) {
                let now = new Date().getTime();
                if (item.expire < now && item.type != 'error') {
                    return false;
                } else {
                    return true;
                }
            });
        },

        clearErrorsFeedback (state) {
            state.feedback = state.feedback.filter( function(item) {
                return item.type != 'error';
            });
        },

        processing(state, data) {
            state.processing = data;
        },
    },

    actions: {
        setEditing({ commit, state }, editing) {
            commit('setEditing', editing);
        },

        setPage({ commit, state }, page) {
            commit('setPage', page);
        },

        addContentElement({ commit, state }, contentElement) {
            commit('addContentElement', contentElement);
        },

        addFeedback({ commit, state }, feedback) {

            var expire;

            if (feedback.message) {

                if (parseInt(feedback.expire) > 0) {
                    expire = feedback.expire;
                }

                if (expire == undefined) {
                    expire = new Date().getTime() + 4900;
                }

                let item = {
                    type: feedback.type,
                    message: feedback.message,
                    link: feedback.link,
                    expire: expire,
                    input: feedback.input,
                    assert: feedback.type + '|' + feedback.message,
                    key: [...Array(30)].map(() => Math.random().toString(36)[3]).join(''),
                };

                if (!state.feedback.find(element => element.message === item.message)) {
                    commit('addFeedback', item);

                    // clear out any info and successes automatically

                    setTimeout(function() {
                       store.dispatch('clearFeedback');
                    }, 5000);
                }

            }

        },

        clearFeedback({ commit }) {
            commit('clearFeedback');
        },

        clearErrorsFeedback({ commit }) {
            commit('clearErrorsFeedback');
        },

        processing({ commit, state }, data) {
            commit('processing', data);
        },

    }
});

export default store
