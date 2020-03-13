import Vue from "vue";
Vue.config.productionTip = false; // turn off the console log that says we are using Vue :)

import lodash from "lodash";
Object.defineProperty(Vue.prototype, "$lodash", { value: lodash });

import axios from "axios";
Object.defineProperty(Vue.prototype, "$http", { value: axios });

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

Object.defineProperty(Vue.prototype, "$eventer", { value: new Vue() });

import store from './Store';

import ContentEditor from '@/Components/ContentEditor.vue'
import EditingButton from '@/Components/EditingButton.vue'
import Feedback from '@/Components/Feedback.vue'
import PageTree from '@/Components/PageTree.vue'
import PageList from '@/Components/PageList.vue'
import Processing from '@/Components/Processing.vue'

Vue.component('page-list', PageList);

const app = new Vue({
    el: "#app",
    store,
    
    components: {
        'content-editor': ContentEditor,
        'editing-button': EditingButton,
        'feedback': Feedback,
        'page-tree': PageTree,
        'processing': Processing,
    }
});
