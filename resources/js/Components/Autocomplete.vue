<template>

    <div class="form autocomplete relative" :class="{'mb-4' : !noMargin, 'flex' : flex}" :dusk="'autocomplete-' + dusk">

        <div class="flex-1">
            <div class="flex items-end">
                <div class="flex-1">

                    <transition name="text-sm">
                        <div class="label" v-if="showLabel & !hideLabel">
                            <label for="name">{{ label ? label : placeholder }}</label>
                        </div>
                    </transition>

                    <div class="">

                        <div class="absolute z-10 text-gray-500 py-2 px-2">
                            <i class="fas fa-search"></i>
                        </div>

                        <input type="search"
                            id="name" 
                            name="terms" 
                            :dusk="'autocomplete-search-' + (dusk ? dusk : (model ? model : name))"
                            v-model="terms" 
                            class="icon"
                            autocomplete="off" 
                            :placeholder="placeholder ? placeholder : (multiple ? 'Add...' : 'Search')"
                            ref="input"

                            @focus="showResults()"
                            @blur="hideResults()"

                            @keyup.enter="selectCurrent" 
                            @keyup.up="selectPrev" 
                            @keyup.down="selectNext"
                            @keydown.tab="selectCurrent" 

                            @keydown.up.stop.prevent=""
                            @keydown.down.stop.prevent=""
                            @keyup.esc="$emit('esc')"
                        >

                    </div>

                </div>

                <remove v-if="clear" class="py-1" @remove="remove()"></remove>
            </div>

            <div class="w-full relative" v-show="results.length > 0 && resultsVisible">
                <transition-group tag="div" name="search-results" :style="'max-height: ' + maxHeight + 'px'" class="shadow-lg overflow-y-scroll absolute w-full" dusk="autocomplete-results">
                    <div class="px-2 py-1 relative z-20 w-full cursor-pointer flex" 
                        v-for="result in $lodash.take(results, 100)" 
                        @click="result.add ? addModel() : select(result)"
                        :key="'result-' + ( result.class_name ? result.class_name : model ) + '-' + result.id"
                        :dusk="'result-' + result.id"
                        :selected="result.selected ? 'selected' : false"
                        :class="result.selected ? 'bg-yellow-100 text-primary-900' : 'bg-gray-100 odd:bg-gray-200 text-gray-700 hover:bg-white hover:text-primary-500'"
                    >
                        <div class="flex-1">{{ result.search_label }}</div>
                        <div class="text-gray-500 italic" v-if="result.count">{{ result.count }}</div>
                    </div>
                </transition-group>
            </div>
        </div>

        <div class="flex-2 overflow-hidden" :class="flex ? 'flex items-center' : 'mt-2'" v-if="multiple">

            <component v-if="model"
                 :is="model"
                 v-for="(item, index) in items"
                 :key="item.id + '-' + index"
                 v-bind="childProps(item)"
                 @remove="$emit('remove', $event)"
                 :remove="true"
                 :flex="flex"
             ></component>

            <div v-if="!model" class="">
                <transition-group name="row">
                    <div v-for="(item, index) in items" 
                        class="flex items-center pl-2 pr-1 py-1 bg-gray-100 m-1 border" 
                        :key="( item.class_name ? item.class_name : model ) + '-' + item.id"
                    >
                        <div class="flex-1 whitespace-no-wrap">{{ item.search_label }}</div>
                        <remove :dusk="'remove-' + name + '-' + item.id" @remove="$emit('remove', item)"></remove>
                    </div>
                </transition-group>
            </div>
            
        </div>

    </div>

</template>

<script>

    export default {

        components: {
            'remove': () => import(/* webpackChunkName: "remove" */ '@/Components/Remove'),
            'tag': () => import(/* webpackChunkName: "tag" */ '@/Models/Tag.vue'),
        },

        mixins: [],

        props: [
            'url',
            'placeholder',
            'multiple',
            'model',
            'name',
            'value',
            'afterSelect',
            'requestData',
            'clear',
            'hideLabel',
            'noMargin',
            'add',
            'addUrl',
            'keepResults',
            'recent',
            'dusk',
            'label',
            'flex',
        ],

        data() {
            return {
                terms: '',
                results: [],
                item: {},
                items: [],
                clicked: false,
                modalItem: null,
                windowHeight: 0,
                scrollTop: 0,
                resultsVisible: true,
            }
        },

        computed: {
            showLabel() {
                if (this.multiple) {
                    return true;
                } else {
                    if (this.item) {
                        return this.item.id > 0;
                    } else {
                        return false;
                    }
                }
            },

            maxHeight() {
                if (this.windowHeight > 0) {

                    let height = this.windowHeight - (this.$el.offsetTop + 165) + this.scrollTop;

                    if (height < 200) {
                        return 200;
                    }
                    return height;
                } else {
                    return 256;
                }
            }
        },

        watch: {

            terms() {
                if (!this.clicked) {
                    this.search();
                } else {
                    this.clicked = false;
                }
            },

            value() {
                this.setItems();
            },

            item() {
                this.$emit('input', this.item);
            }, 

            items() {
                this.$emit('input', this.items);
            },

        },

        mounted() {
            this.setItems();

            const listener = _.debounce(event => {
                this.setWindowHeight();
            }, 500);

            window.addEventListener('resize', listener, { passive: true });
            window.addEventListener('scroll', listener, { passive: true });
            this.$once('hook:destroyed', () => {
                window.removeEventListener('resize', listener);
                window.removeEventListener('scroll', listener);
            });

            setTimeout(this.setWindowHeight, 500);
            //this.setWindowHeight();
        },

        methods: {

            search: _.debounce( function() {
                if (this.terms) {
                    if (this.terms.length >= 1) {

                        let input = {autocomplete: true, terms: this.terms};
                        if (this.requestData) {
                            input = this.$lodash.merge(input, this.requestData); 
                        }

                        this.$http.post(this.url, input).then( response => {
                            if (response.data.results) {
                                this.results = response.data.results;
                            } else {
                                this.results = [];
                            }

                            if (this.add && false) {

                                if (!this.$lodash.find(this.results, result => {
                                    return result.search_label === this.terms;
                                })) {
                                    this.results.push({
                                        id: 0,
                                        search_label: this.addUrl ? 'Add ' + this.terms : 'Add New ' + this.$lodash.startCase(this.model),
                                        add: true,
                                        selected: false,
                                    });
                                }
                            }
                        }, error => {
                            //this.processErrors(error.response);
                        });
                    } else {
                        this.results = [];
                    }
                } else {
                    this.results = [];
                }
            }, 250),

            select: function(result) {

                if (this.add) {
                    this.modalItem = null;
                }

                if (result.search_label != this.terms) {
                    this.clicked = true;
                }

                if (this.multiple) {

                    if (! this.$lodash.find( this.items, {id: result.id, class_name: result.class_name })) {
                        this.items.push(result);
                    }
                    if (!this.keepResults) {
                        this.terms = '';
                    }
                } else {
                    this.item = result;
                    this.terms = result.search_label;
                }

                this.results = [];
                this.resultsVisible = true;

                let postFunction = this.afterSelect;
                let postOptions = '';

                if (postFunction) {
                    if (postFunction.indexOf('(')) {
                        postOptions = postFunction.substring(postFunction.indexOf('(') + 1, postFunction.indexOf(')'));
                        postFunction = postFunction.substring(0, postFunction.indexOf('('));
                    }

                    if (this.$lodash.isFunction(this[postFunction])) {
                        this[postFunction](postOptions);
                    }
                }


            },

            childProps: function(item) {
                return {[this.model]: item};
            },

            setItems: function() {
                if (this.multiple) {
                    if (this.value) {
                        if (this.$lodash.isArray(this.value)) {
                            this.items = this.value;
                        } else {
                            this.items.push(this.value);   
                        }
                    }
                } else {
                    this.item = this.value;
                    this.clicked = true;
                    this.terms = this.value ? this.value.search_label : '';
                }
            },

            // AFTER SELECT

            loadPage: function(page) {
                let url = page.replace(/ID/, this.item.id);
                this.$inertia.visit(url);
            },

            //  SELECT FUNCTIONS

            selectNext: function(e) {

                if (this.results.length > 0) {
                    let current = this.$lodash.findIndex(this.results, function(result) {
                        return result.selected;
                    });

                    this.$lodash.forEach(this.results, function(result) {
                        result.selected = false;
                    });

                    if (current != -1) {
                        if (this.results[(current + 1)] != undefined) {
                            this.results[(current + 1)].selected = true;
                        } else {
                            if (this.results.length > 1) {
                                this.results[0].selected = true;
                            }
                        }
                    } else {
                        this.results[0].selected = true;
                    }
                }

            },

            selectPrev: function(e) {

                if (this.results.length > 0) {
                    let current = this.$lodash.findIndex(this.results, function(result) {
                        return result.selected;
                    });

                    this.$lodash.forEach(this.results, function(result) {
                        result.selected = false;
                    });

                    if (current != -1) {
                        if (this.results[(current - 1)] != undefined) {
                            this.results[(current - 1)].selected = true;
                        } else {
                            if (this.results.length > 1) {
                                this.results[(this.results.length - 1)].selected = true;
                            }
                        }
                    } else {
                        this.results[(this.results.length - 1)].selected = true;
                    }
                }
            
            },

            selectCurrent: function(e) {

                if (this.results) {
                    if (this.results.length > 0) {

                        let current = this.$lodash.findIndex(this.results, function(result) {
                            return result.selected;
                        });

                        let result = this.results[current];

                        if (result) {
                            if (result.add) {
                                this.addModel();
                            } else {
                                this.select(result);
                            }
                        }

                    }
                }

            },

            addModel: function() {

                if (this.addUrl) {

                    let input = {
                        'name': this.terms,
                    };
                    
                    this.$http.post(this.addUrl, input).then( response => {
                        this.select( response.data[this.model] );
                    }, error => {
                        this.processErrors(error.response);
                    });

                } else {
                    this.modalItem = 'form-' + this.model;
                }
            },

            remove: function() {
                if (this.multiple) {
                    this.terms = '';
                } else {
                    this.item = {};
                    this.terms = '';
                }
                this.$emit('clear');
                this.$refs.input.focus();
            },

            setWindowHeight: function() {
                //this.windowHeight = document.body.offsetHeight;
                if (this.results.length) {
                    this.windowHeight = window.innerHeight;
                    this.scrollTop = window.scrollY;
                }
            },

            hideResults: _.debounce(function() {
                this.resultsVisible = false;
            }, 500),

            showResults: function() {
                this.resultsVisible = true;
            }
        },

    }
</script>

<style>

@keyframes search-results {
    0% {
        opacity: 0;
        max-height: 0px;
    }
    100%   {
        opacity: 1;
        max-height: 40px;
    }
}

.search-results-enter-active {
    animation: search-results var(--transition-time) ease-out;
}

.search-results-leave-active {
    animation: search-results var(--transition-time) reverse;
}

</style>
