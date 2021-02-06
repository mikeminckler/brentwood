<template>

    <div class="flex items-center justify-center w-full relative">
        <div class="relatice form max-w-sm bg-gray-100 border border-gray-200 px-4 py-2 md:px-8 md:py-4 rounded-lg overflow-hidden mt-4" id="inquiry-form">

            <transition-group :name="transitionDirection" tag="div" class="relative mt-8 first:mt-0">

                <div class="relative" key="step1" v-if="currentStep === 'Contact Information'">

                    <h3 class="mb-4">{{ currentStep }}</h3>

                    <div class="input">
                        <form-label label="Contact Name" :value="form.name"></form-label>
                        <div class=""><input type="text" id="name" v-model="form.name" class="" placeholder="Parent or Student Name" /></div>
                        <form-error :errors="errors" name="name" :show="showErrors"></form-error>
                    </div>

                    <div class="input">
                        <form-label label="Contact Email" :value="form.email"></form-label>
                        <div class=""><input type="email" id="email" v-model="form.email" class="" placeholder="Contact Email" /></div>
                        <form-error :errors="errors" name="email" :show="showErrors"></form-error>
                    </div>

                    <div class="input hidden">
                        <form-label label="Contact Phone Number" :value="form.phone"></form-label>
                        <div class=""><input type="text" inputmode="tel" id="phone" v-model="form.phone" class="" @keyup.enter="nextStep()" placeholder="Contact Phone Number" /></div>
                        <form-error :errors="errors" name="phone" :show="showErrors"></form-error>
                    </div>

                </div>

                <div class="" key="step2" v-if="currentStep === 'Student Information'">

                    <h3>{{ currentStep }}</h3>

                    <div class="mt-4">
                        <div class="input-label">Start Year</div>
                        <form-error :errors="errors" name="target_year" :show="showErrors"></form-error>
                        <div class="flex items-center flex-wrap">
                            <div v-for="year in years"
                                 :key="'year-' + year"
                                 class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                :class="year === form.target_year ? 'bg-primary text-white font-bold' : 'bg-white hover:text-gray-800'" 
                                 @click="form.target_year = year"
                                 >{{ year }}-{{ year + 1 }}</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="">Start Grade</div>
                        <form-error :errors="errors" name="target_grade" :show="showErrors"></form-error>
                        <div class="flex items-center flex-wrap">
                            <div v-for="grade in grades"
                                 :key="'grade-' + grade"
                                 class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                :class="grade === form.target_grade ? 'bg-primary text-white font-bold' : 'bg-white hover:text-gray-800'" 
                                 @click="form.target_grade = grade"
                                 >Grade {{ grade }}</div>
                            <div class="flex-1 mr-4 my-1 px-4">&nbsp;</div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="">Student Type</div>
                        <form-error :errors="errors" name="student_type" :show="showErrors"></form-error>
                        <div class="flex items-center flex-wrap w-full">
                            <div v-for="type in types"
                                 :key="'type-' + type"
                                 class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                :class="type === form.student_type ? 'bg-primary text-white font-bold' : 'bg-white hover:text-gray-800'" 
                                 @click="form.student_type = type"
                                 >{{ type }}</div>
                        </div>
                    </div>

                </div>

                <div class="" key="step3" v-if="currentStep === 'Student Interests'">

                    <h3>{{ currentStep }}</h3>

                    <div class="mt-4">Select the items that the student is interested in.</div>

                    <tags-selector :tags="tags" 
                         :ignore-tags="['Boarding Student', 'Day Student', 'Admissions']"
                        :selected-tags="form.tags" 
                        @selected="toggleTag($event)"
                    ></tags-selector>

                </div>

                <div class="" key="step4" v-if="currentStep === 'Livestreams'">

                    <h3>Upcoming {{ livestreamTitle }}</h3>

                    <div class="" v-if="livestreamTitle === 'Open Houses'">
                        <p>We invite you to join our admissions team for an <span class="font-bold">interactive online presentation</span> where you can <span class="font-bold">ask questions</span> and find out why so many students choose Brentwood for their high school experience. Find a date that best suits your family below.</p>
                    </div>

                    <form-error :errors="errors" name="livestream" :show="showErrors"></form-error>
                    <div class="mt-4">
                        <div class="flex cursor-pointer px-2 py-1" 
                             :class="form.livestream ? (form.livestream.id === livestream.id ? 'bg-green-100 text-gray-800' : 'bg-white odd:bg-gray-200 hover:text-gray-800') : 'bg-white odd:bg-gray-200 hover:text-gray-800'"
                            v-for="livestream in livestreams" 
                            @click="toggleLivestream(livestream)"
                        >
                            <div class="icon" v-show="form.livestream ? (form.livestream.id === livestream.id ? false : true) : true"><i class="far fa-circle"></i></div>
                            <div class="icon" v-show="form.livestream ? (form.livestream.id === livestream.id ? true : false) : false"><i class="fas fa-check-circle"></i></div>
                            <div class="pl-2">{{ $moment(livestream.start_date).format('dddd MMMM Do h:mmA') }}</div>
                        </div>
                    </div>
                </div>

                <div class="" key="step5" v-if="currentStep === 'Review Information'">

                    <div class="mt-4">

                        <h3>{{ currentStep }}</h3>

                        <div class="mt-4">
                            <form-label label="Contact Name" :value="form.name"></form-label>
                            <div class="input"><input type="text" id="name" v-model="form.name" class="" placeholder="Parent or Student Name" /></div>

                            <form-label label="Contact Email" :value="form.email"></form-label>
                            <div class="input"><input type="email" id="email" v-model="form.email" class="" placeholder="Contact Email" /></div>

                            <form-label label="Contact Phone Number" :value="form.phone"></form-label>
                            <div class="input hidden"><input type="text" id="phone" v-model="form.phone" class="" @keyup.enter="nextStep()" placeholder="Contact Phone Number" v-if="form.phone" /></div>

                            <form-label label="Start Year" :value="form.target_year"></form-label>
                            <div class="fake-input" v-if="form.target_year">{{ form.target_year }}-{{ form.target_year + 1 }}</div>

                            <form-label label="Start Grade" :value="form.target_grade"></form-label>
                            <div class="fake-input" v-if="form.target_grade">Grade {{ form.target_grade }}</div>

                            <form-label label="Student Type" :value="form.student_type"></form-label>
                            <div class="fake-input" v-if="form.student_type">{{ form.student_type }}</div>

                            <form-label label="Student Interests" :value="filteredFormTags.length"></form-label>
                            <div class="fake-input" v-if="filteredFormTags.length">
                                <div class="" v-for="tag in filteredFormTags">{{ tag.name }}</div>
                            </div>

                            <div class="" v-if="form.livestream">
                                <form-label :label="'Selected ' + livestreamTitle" :value="form.livestream"></form-label>
                                <div class="fake-input" v-if="form.livestream">{{ $moment(form.livestream.start_date).format('dddd MMMM Do h:mmA') }}</div>
                            </div>

                        </div>

                    </div>
                </div>

            </transition-group>

            <div class="flex justify-around items-center mt-4">
                <div class="flex items-center hover:bg-white px-4 py-2 transition-opacity transition" :class="currentStep === steps[0] ? 'opacity-0' : 'opacity-1 cursor-pointer'" @click="prevStep()">
                    <div class="icon mr-2"><i class="fas fa-chevron-left"></i></div>
                    <div class="">Back</div>
                </div>

                <div class="flex flex-1 mx-2">
                    <div class="mx-2 transition transition-all" 
                         :class="stepClass(step)"
                         v-for="step in steps"
                         @click="goToStep(step)"
                    >
                        <div class="icon" v-if="!showCheck(step)"><i class="fas fa-circle"></i></div>
                        <div class="icon" v-if="showCheck(step)"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>

                <div class="relative flex items-center px-4 py-2 cursor-pointer text-white font-bold" :class="errors.length ? 'bg-primary' : 'bg-green-500'" @click="nextStep()">
                    <div class="">{{ nextText }}</div>
                    <div class="ml-2"><i class="fas fa-chevron-right"></i></div>
                </div>
            </div>

        </div>

    </div>

</template>

<script>

    import Dates from '@/Mixins/Dates';
    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [
            'showStudentInfo',
            'showInterests',
            'showLivestreams',
            'showLivestreamsFirst',
            'livestream',
            'livestreams',
        ],

        mixins: [Feedback, Dates],

        components: {
            'form-label': () => import(/* webpackChunkName: "form-label" */ '@/Components/FormLabel.vue'),
            'form-error': () => import(/* webpackChunkName: "form-error" */ '@/Components/FormError.vue'),
            'tags-selector': () => import(/* webpackChunkName: "tags-selector" */ '@/Components/TagsSelector.vue'),
        },

        data() {
            return {
                showErrors: false,
                url: '',
                transitionDirection: 'inquiry-form-forward',
                currentStep: 'Start',
                allSteps: [
                    'Contact Information',
                    'Student Information',
                    'Student Interests',
                    'Livestreams',
                    'Review Information',
                ],
                types: ['Boarding', 'Day'],

                tags: [],

                form: {
                    name: '',
                    email: '',
                    phone: '',
                    target_grade: null,
                    target_year: null,
                    student_type: null,
                    tags: [],
                    livestream: null,
                    //gender: '',
                },

                grades: [8,9,10,11,12],
            }
        },

        computed: {

            steps() {

                let steps = this.$lodash.clone(this.allSteps);

                if (!this.showStudentInfo) {
                    steps = this.$lodash.remove(steps, step => {
                        return step !== 'Student Information';
                    });
                }

                if (!this.showInterests || !this.tags.length) {
                    steps = this.$lodash.remove(steps, step => {
                        return step !== 'Student Interests';
                    });
                }

                if (!this.showLivestreams) {
                    steps = this.$lodash.remove(steps, step => {
                        return step !== 'Livestreams';
                    });
                }

                if (this.livestream) {
                    steps = this.$lodash.remove(steps, step => {
                        return step !== 'Livestreams';
                    });
                }

                if (!this.livestreams) {
                    steps = this.$lodash.remove(steps, step => {
                        return step !== 'Livestreams';
                    });
                }

                if (this.showLivestreams && this.showLivestreamsFirst) {
                    steps = this.$lodash.sortBy(steps, step => {
                        return step === 'Livestreams' ? 0 : 1;
                    });
                }

                this.currentStep = steps[0];
                return steps;
            },

            errors() {

                let errors = [];

                if (this.currentStep === 'Contact Information') {

                    if (!this.form.name) {
                        errors.push({name: 'Please provide a contact name'});
                    } else if (this.form.name.length < 3) {
                        errors.push({name: 'The contact name must be at least 3 characters'});
                    }

                    if (!this.form.email) {
                        errors.push({email: 'Please provide a contact email'});
                    } else if (!this.validEmail) {
                        errors.push({email: 'Please provide a valid email address'});
                    }
                }

                if (this.currentStep === 'Student Information') {
                    
                    if (!this.form.target_year) {
                        errors.push({target_year: 'Please select an entry year'});
                    }

                    if (!this.form.target_grade) {
                        errors.push({target_grade: 'Please select an entry grade'});
                    }

                    if (!this.form.student_type) {
                        errors.push({student_type: 'Please select a student type'});
                    }
                }

                if (this.currentStep === 'Student Interests') {

                    //return this.currentStep === 'Student Interests' || this.currentStep === 'Livestreams' || this.currentStep === 'Review Information';
                }

                if (this.currentStep === 'Livestreams') {
                    if (this.showLivestreamsFirst) {
                        if (!this.form.livestream) {
                            errors.push({livestream: 'Please select a session'});
                        }
                    }
                }

                if (this.currentStep === 'Review Information') {

                }

                return errors;
            },

            currentIndex() {
                return this.$lodash.findIndex(this.steps, step => {
                    return step === this.currentStep;
                });
            },

            years() {
                let years = [];

                let currentYear = new Date().getFullYear();
                // if we are in august remove the upcoming year
                if (new Date().getMonth() > 6) {
                    currentYear ++;
                }
                let year = currentYear;
                while (year < (currentYear + 2)) {
                    years.push(year);
                    year++;
                }
                return years;
            },

            livestreamTitle() {

                if (!this.livestreams) {
                    return 'Livestreams';
                }

                let tags = [];

                this.$lodash.each(this.livestreams, livestream => {
                    if (livestream.tags.length) {
                        this.$lodash.each(livestream.tags, tag => {
                            if (this.$lodash.findIndex(tags, t => {
                                return t === tag.name;
                            }) < 0) {
                                tags.push(tag.name);
                            }
                        });
                    }
                });

                if (tags.length > 1) {
                    return this.$lodash.join(tags, ' & ');
                } else {
                    return tags[0];
                }

            },

            nextText() {
                if (this.currentIndex === (this.steps.length - 1)) {
                    return 'Finish';
                } else {
                    return 'Next';
                }
            },

            validEmail() {
                if (!this.form.email.length) {
                    return false;
                }
                let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                let email = String(this.form.email);
                return Boolean(email.match(mailformat));
            },

            filteredFormTags() {
                return this.$lodash.filter(this.form.tags, tag => {
                    return tag.name !== 'Boarding Student' && tag.name !== 'Day Student';
                });
            },

            boardingTag() {
                return this.$lodash.find(this.tags[0].tags, tag => {
                    return tag.name === 'Boarding Student';
                });
            },

            dayTag() {
                return this.$lodash.find(this.tags[0].tags, tag => {
                    return tag.name === 'Day Student';
                });
            },
        },

        mounted() {
            this.loadTags();

            if (this.livestream) {
                this.form.livestream = this.livestream;
            }
            
            if (this.$store.state.user.id) {
                this.form.name = this.$store.state.user.name;
                this.form.email = this.$store.state.user.email;
            }
        },

        watch: {
            'form.student_type': function() {

                if (this.form.student_type === 'Boarding') {
                    if (!this.$lodash.find(this.form.tags, this.boardingTag)) {
                        this.form.tags.push(this.boardingTag);
                    }
                    let index = this.$lodash.findIndex(this.form.tags, this.dayTag);
                    if (index >= 0) {
                        this.form.tags.splice(index, 1);
                    }
                }

                if (this.form.student_type === 'Day') {
                    if (!this.$lodash.find(this.form.tags, this.dayTag)) {
                        this.form.tags.push(this.dayTag);
                    }
                    let index = this.$lodash.findIndex(this.form.tags, this.boardingTag);
                    if (index >= 0) {
                        this.form.tags.splice(index, 1);
                    }
                }
            },

        },

        methods: {

            showCheck: function(step) {
                
                if (!step) {
                    step = this.currentStep;
                }

                let index = this.$lodash.findIndex(this.steps, s => {
                    return s === step;
                });

                if (index < this.currentIndex) {
                    return true;
                } else if (index > this.currentIndex) {
                    return false;
                } else {

                    if (step === 'Contact Information' || step === 'Student Information') {
                        return this.errors.length ? false : true;
                    } else {
                        return false;
                    }
                }

            },

            stepClass: function(step) {

                let classes = '';

                let index = this.$lodash.findIndex(this.steps, s => {
                    return s === step;
                });

                if (step === 'Review Information') {
                    classes += ' hidden';
                }
                
                if (this.showCheck(step)) {
                    classes += ' text-green-500 hover:text-green-400 cursor-pointer';
                } else if (this.currentStep === step) {
                    classes += ' text-gray-600';
                } else {
                    classes += ' text-gray-400';
                }

                return classes;

            },

            goToStep: function(step) {

                let index = this.$lodash.findIndex(this.steps, s => {
                    return s === step;
                });

                if (index < this.currentIndex) {
                    this.transitionDirection = 'inquiry-form-backward';
                } else {
                    this.transitionDirection = 'inquiry-form-forward';
                }

                if (this.showCheck(step)) {
                    this.currentStep = step;
                }

            },

            nextStep: function() {
                
                if (this.errors.length > 0) {
                    this.showErrors = true;
                } else {
                    this.showErrors = false;
                }

                if (this.errors.length === 0 || this.$store.state.editing) {

                    this.transitionDirection = 'inquiry-form-forward';

                    if (this.currentIndex < (this.steps.length - 1)) {

                        this.currentStep = this.steps[this.currentIndex + 1];
                        this.scrollToForm();

                    } else if (this.currentIndex === (this.steps.length - 1)) {

                        if (!this.$store.state.editing) {
                            this.submit();
                        }

                    }
                }

            },

            prevStep: function() {
                this.transitionDirection = 'inquiry-form-backward';

                if (this.currentIndex > 0) {
                    this.currentStep = this.steps[this.currentIndex - 1];
                    this.scrollToForm();
                }
            },

            scrollToForm: function() {
                if (window.innerWidth < 768) {
                    let elm = document.getElementById('inquiry-form');

                    if (elm.offsetTop < window.scrollY) {
                        window.scrollTo({top: elm.offsetTop, behavior: 'smooth'});
                    }
                }
            },

            submit: function() {

                this.$http.post('/inquiry', this.form).then( response => {
                    this.processSuccess(response);
                    window.location.href = response.data.inquiry.url;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            loadTags: function() {

                this.$http.get('/inquiry/tags').then( response => {
                    this.tags = response.data.tags;
                }, error => {
                    this.processErrors(error.response);
                });

            },

            toggleTag: function(tag) {
                this.form.tags = this.$lodash.xor(this.form.tags, [tag]);
            },

            toggleLivestream: function(livestream) {

                if (!this.form.livestream) {
                    this.form.livestream = livestream;
                } else if (this.form.livestream.id === livestream.id) {
                    this.form.livestream = null;
                } else {
                    this.form.livestream = livestream;
                }

            },
        },

    }
</script>

<style>

@keyframes inquiry-form-in {
    0% {
        opacity: 0;
        transform: translateX(100%);
    }
    100% {
        opacity: 1;
        transform: translateX(0%);
    }
}

@keyframes inquiry-form-out {
    0% {
        opacity: 1;
        transform: translateX(0%);
    }
    100% {
        opacity: 0;
        transform: translateX(-100%);
    }
}

.inquiry-form-forward-enter-active {
    animation: inquiry-form-in calc(var(--transition-time) * 2) ease-out;
}

.inquiry-form-forward-leave-active {
    @apply absolute;
    animation: inquiry-form-out calc(var(--transition-time) * 2) ease-out;
}

.inquiry-form-backward-enter-active {
    animation: inquiry-form-out calc(var(--transition-time) * 2) reverse;
}

.inquiry-form-backward-leave-active {
    @apply absolute;
    animation: inquiry-form-in calc(var(--transition-time) * 2) reverse;
}

</style>
