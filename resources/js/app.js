import Vue from "vue";
Vue.config.productionTip = false; // turn off the console log that says we are using Vue :)

import lodash from "lodash";
Object.defineProperty(Vue.prototype, "$lodash", { value: lodash });

import axios from "axios";
Object.defineProperty(Vue.prototype, "$http", { value: axios });

axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

Object.defineProperty(Vue.prototype, "$eventer", { value: new Vue() });

import store from './Store';

import PageEditor from '@/Components/PageEditor.vue'
import EditingButton from '@/Components/EditingButton.vue'
import Feedback from '@/Components/Feedback.vue'
import PageTree from '@/Components/PageTree.vue'
import Processing from '@/Components/Processing.vue'
import SavingIndicator from '@/Components/SavingIndicator.vue'

import ContentElementsEditor from '@/Components/ContentElementsEditor.vue'
Vue.component('content-elements-editor', ContentElementsEditor);

const app = new Vue({
    el: "#app",
    store,
    
    components: {
        'page-editor': PageEditor,
        'editing-button': EditingButton,
        'feedback': Feedback,
        'page-tree': PageTree,
        'processing': Processing,
        'saving-indicator': SavingIndicator,
    }
});
