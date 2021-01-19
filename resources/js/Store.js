import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex)

const store = new Vuex.Store({
    state: {
        user: {},
        wsState: '',
        editing: false,
        editorMounted: false,
        showMenu: false,
        feedback: [],
        saving: [],
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
            type: null,
            footer_fg_photo: {},
            footer_bg_photo: {},
            tags: [],
        },
        pageTree: {},
        youtubeReady: false,
        dragging: false,
        pageLoading: false,
        locale: 'en',
    },

    mutations: {

        setUser (state, user) {
            state.user = user;
        },

        setWsState (state, wsState) {
            state.wsState = wsState;
        },

        setEditing(state, editing) {
            state.editing = editing;
        },

        editorMounted(state, isMounted) {
            state.editorMounted = isMounted;
        },

        toggleMenu(state, showMenu) {
            state.showMenu = showMenu;
        },

        startSaving(state, saving) {
            state.saving.push(saving);
        },

        completeSaving(state, saving) {
            let index = state.saving.findIndex(save => save === saving);
            state.saving.splice(index, 1);
        },

        setPage(state, page) {
            state.page = page;
        },

        resetPage(state) {
            publish_at: '',
            state.page = {
                id: 0,
                name: '',
                parent_page_id: '',
                unlisted: 0,
                sort_order: 0,
                content_elements: [],
                publish_at: '',
                footer_fg_photo: {},
                footer_bg_photo: {},
            };
        },

        setPageTree(state, pageTree) {
            state.pageTree = pageTree;
        },

        setYoutubeReady(state) {
            state.youtubeReady = true;
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

        setDragging(state, dragging) {
            state.dragging = dragging;
        },

        setPageLoading (state, loading) {
            state.pageLoading = loading;
        },

        setLocale(state, locale) {
            state.locale = locale;
        },
    },

    actions: {

        setUser({ commit, state }, user) {
            commit('setUser', user);
        },

        setWsState({ commit, state }, wsState) {
            commit('setWsState', wsState);
        },

        setEditing({ commit, state }, editing) {
            commit('setEditing', editing);
        },

        editorMounted({ commit, state }, isMounted) {
            commit('editorMounted', isMounted);
        },

        toggleMenu({ commit, state }) {
            let showMenu = !state.showMenu;
            commit('toggleMenu', showMenu);
        },

        startSaving({ commit, state }, saving) {
            commit('startSaving', saving);
        },

        completeSaving({ commit, state }, saving) {
            commit('completeSaving', saving);
        },

        setPage({ commit, state }, page) {
            if (page.type) {
                console.log('SETTING PAGE IN VUEX');
                commit('setPage', page);
            }
        },

        resetPage({ commit, state }) {
            commit('resetPage');
        },

        setPageTree({ commit, state }, pageTree) {
            commit('setPageTree', pageTree);
        },

        setYoutubeReady({ commit, state }) {
            commit('setYoutubeReady');
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

        setDragging({ commit, state }, dragging) {
            commit('setDragging', dragging);
        },

        setPageLoading({ commit, state }, loading) {
            //console.log('PAGE LOADING: ' + loading);
            commit('setPageLoading', loading);
        },

        setLocale({ commit, state }, locale) {
            commit('setLocale', locale);
        },

    }
});

export default store
