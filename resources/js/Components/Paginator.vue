<template>

    <div class="">

        <div class="grid" :class="'grid-' + (grid ? grid : resource)">

            <slot name="header"></slot>

            <component :is="resource + '-row'"
                 v-for="(item, index) in $lodash.values(paginator.data)"
                 :key="resource + '-' + item.id"
                 @selected="$emit('selected', $event)"
                 :index="index"
                 :item="item"
             ></component>
        </div>

        <div class="mt-4" v-if="!$lodash.values(paginator.data).length">There are no {{ resource }} to view.</div>

        <div class="flex justify-center">
            <transition-group
                class="flex items-center justify-center"
                tag="div"
                name="numbers"
                v-show="paginator.total > count"
            >

                <div :class="paginator.prev_page_url ? 'cursor-pointer hover:bg-white hover:text-primary-700' : 'text-gray-400'" 
                    key="0"
                    class="w-6 h-6 p-1 bg-gray-100 flex items-center justify-center border-l border-t border-b" 
                    @click="page > 1 ? page = paginator.current_page - 1 : null"
                >
                    <span class="fas fa-angle-left"></span>
                </div>

                <div v-for="pageLink in pages" :key="pageLink">
                    <div class="w-8 h-8 cursor-pointer bg-white flex items-center justify-center hover:text-primary-500 shadow-lg"
                        :class="paginator.current_page == pageLink ? 'font-bold' : ''"
                        v-if="$lodash.isNumber(pageLink)" @click="page = pageLink">{{ pageLink }}</div>
                    <div class="w-8 h-8 cursor-pointer bg-gray-100 flex items-center justify-center hover:bg-white hover:text-primary-700" v-else>{{ pageLink.trim() }}</div>
                </div>

                <div :class="paginator.next_page_url ? 'cursor-pointer hover:bg-white hover:text-primary-700' : 'text-gray-400'" 
                    key="1000"
                    class="w-6 h-6 p-1 bg-gray-100 flex items-center justify-center border-r border-t border-b" 
                    @click="page < pages.length ? page = paginator.current_page + 1 : null"
                >
                    <span class="fas fa-angle-right"></span>
                </div>

            </transition-group>
        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        mixins: [Feedback],

        props: [
            'resource', 
            'grid',
        ],

        components: {
            'blogs-row': () => import(/* webpackChunkName: "blogs-row" */ '@/Models/BlogsRow.vue'),
            'inquiries-row': () => import(/* webpackChunkName: "inquiries-row" */ '@/Models/InquiriesRow.vue'),
            'livestreams-row': () => import(/* webpackChunkName: "livestreams-row" */ '@/Models/LivestreamsRow.vue'),
        },

        data() {
            return {
                page: 1,
                count: 15,
                paginator: {
                    data: [],
                },
                terms: '',
                fields: [],
                sort: '',
                descending: true,
                showSearch: false,

                loadPaginator: _.throttle(function() {
                    this.paginate();
                }, 500),
                search: _.debounce( function() {
                    if (this.page === 1) {
                        this.loadPaginator();
                    } else {
                        this.page = 1;
                    }
                }, 250),
            }
        },

        computed: {
            getString() {
                let ascending = !this.descending ? true : '';
                return '?paginate=true&paginate_page=' + this.page + '&paginate_count=' + this.count + '&sort=' + this.sort + '&ascending=' + ascending;
            },

            pages: function() {

                let arr = [];

                if (this.paginator.last_page > 4) {
                    
                    let mid = this.paginator.current_page - 2;

                    if (mid < 4) {
                        mid = 1;
                    }

                    if (mid > 2) {
                        let start = 1;
                        while (start < 3) {
                            arr.push(start);
                            start++;
                        }
                        arr.push('...');
                    }

                    let target = this.paginator.current_page + 2;
                    if (target > (this.paginator.last_page - 3)) {
                        target = this.paginator.last_page;
                    }

                    while (mid <= target) {
                        if (mid > 0 && mid <= this.paginator.last_page) {
                            arr.push(mid);
                        }
                        mid++;
                    }

                    if ((mid + 2) <= this.paginator.last_page) {
                        let end = this.paginator.last_page - 1;
                        arr.push('... ');
                        while (end <= this.paginator.last_page) {
                            arr.push(end);
                            end++;
                        }
                    }

                } else {

                    let p = 1;

                    while (p <= this.paginator.last_page) {
                        arr.push(p);                    
                        p++;
                    }

                }

                return arr;
            
            },
        },

        watch: {
            page() {
                this.loadPaginator();
            },
            terms() {
                this.search();
            },
        },

        mounted() {
            this.loadPaginator();

            const listener = data => {
                if (data.resource === this.resource){
                    this.loadPaginator();
                }
            };
            this.$eventer.$on('paginate', listener);

            this.$once('hook:destroyed', () => {
                this.$eventer.$off('paginate', listener);
            });

        },

        methods: {

            paginate: function() {
            
                this.startProcessing('Loading ' + this.$lodash.startCase(this.resource));

                //this.paginator.data = [];

                if (this.terms) {

                    this.$http.post('/' + this.resource + '/search', this.input).then( response => {
                        this.paginator = response.data;
                        this.stopProcessing();
                    }, error => {
                        this.stopProcessing();
                        this.processErrors(response);
                    });

                } else {
                    let page = '/' + this.resource + this.getString;

                    this.$http.get(page).then( response => {
                        this.paginator = response.data;
                        this.stopProcessing();
                    }, error => {
                        this.stopProcessing();
                        this.processErrors(response);
                    });
                }

            },
        },

    }
</script>

<style>

@keyframes numbers {
    0% {
        opacity: 0;
        width: 0;
    }
    100%   {
        opacity: 1;
        @apply w-8;
    }
}

.numbers-enter-active {
    animation: numbers var(--transition-time) ease-out;
}

.numbers-leave-active {
    animation: numbers var(--transition-time) reverse;
}

</style>
