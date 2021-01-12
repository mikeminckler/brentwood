<template>

    <div class="" v-if="show">
        <div class="md:flex">

            <div class="flex-1"></div>
            <div class="flex-2">

                <div class="flex items-center justify-center w-full">
                    <div class="form my-4 max-w-sm bg-gray-100 border border-gray-200 px-8 py-4 rounded-lg overflow-hidden">

                        <transition-group :name="transitionDirection" tag="div" class="relative mt-8 first:mt-0">

                            <div class="" key="step1" v-if="currentStep === 1">
                                <div class="input-label"><label for="name">Contact Name</label></div>
                                <div class="input"><input type="text" id="name" v-model="form.name" class="" placeholder="Parent or Student Name" /></div>

                                <div class="input-label"><label for="email">Contact Email</label></div>
                                <div class="input"><input type="text" id="email" v-model="form.email" class="" placeholder="example@example.ca" /></div>

                                <div class="input-label"><label for="phone">Contact Phone Number</label></div>
                                <div class="input"><input type="text" id="phone" v-model="form.phone" class="" placeholder="250-555-5555" /></div>
                            </div>

                            <div class="" key="step2" v-if="currentStep === 2">

                                <div class="mt-4">
                                    <div class="input-label">Start Year</div>
                                    <div class="flex items-center flex-wrap">
                                        <div v-for="year in years"
                                             :key="'year-' + year"
                                             class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                            :class="year === form.target_year ? 'bg-primary text-white font-bold' : 'bg-gray-200 hover:bg-white'" 
                                             @click="form.target_year = year"
                                             >{{ year }}-{{ year + 1 }}</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="">Start Grade</div>

                                    <div class="flex items-center flex-wrap">
                                        <div v-for="grade in grades"
                                             :key="'grade-' + grade"
                                             class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                            :class="grade === form.target_grade ? 'bg-primary text-white font-bold' : 'bg-gray-200 hover:bg-white'" 
                                             @click="form.target_grade = grade"
                                             >Grade {{ grade }}</div>
                                        <div class="flex-1 mr-4 my-1 px-4">&nbsp;</div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <div class="">Student Type</div>

                                    <div class="flex items-center flex-wrap w-full">
                                        <div v-for="type in types"
                                             :key="'type-' + type"
                                             class="flex-1 text-center border px-4 py-2 mr-4 my-1 cursor-pointer whitespace-nowrap"
                                            :class="type === form.student_type ? 'bg-primary text-white font-bold' : 'bg-gray-200 hover:bg-white'" 
                                             @click="form.student_type = type"
                                             >{{ type }}</div>
                                    </div>
                                </div>

                            </div>

                            <div class="" key="step3" v-if="currentStep === 3">
                                <div class="">The applicant is most interested in</div>

                                <div class="">

                                    <div class="" v-for="category in categories">{{ category }}</div>

                                </div>
                            </div>

                            <div class="" key="step4" v-if="currentStep === 4">
                                <div class="mt-4">
                                    <div class="text-gray-500 text-sm">Contact Name</div>
                                    <div class="">{{ form.name }}</div>

                                    <div class="text-gray-500 text-sm mt-2">Contact Email</div>
                                    <div class="">{{ form.email }}</div>

                                    <div class="text-gray-500 text-sm mt-2">Contact Phone Number</div>
                                    <div class="">{{ form.phone }}</div>
                                </div>
                            </div>

                        </transition-group>

                        <div class="flex justify-around items-center mt-4">
                            <div class="flex-1 flex items-center hover:bg-white px-4 py-2 transition-opacity transition" :class="currentStep === 1 ? 'opacity-0' : 'opacity-1 cursor-pointer'" @click="prevStep()">
                                <div class="icon mr-2"><i class="fas fa-chevron-left"></i></div>
                                <div class="">Back</div>
                            </div>

                            <div class="flex flex-1 mx-2">
                                <div class="mx-2 transition transition-all" 
                                     :class="[validateStep(step) ? 'text-green-500 hover:text-green-400 cursor-pointer' : (currentStep === step ? 'text-gray-600' : 'text-gray-300'), step === steps.length ? 'hidden' : '']"
                                     v-for="step in steps"
                                     @click="goToStep(step)"
                                >
                                    <div class="icon" v-if="!validateStep(step)"><i class="fas fa-circle"></i></div>
                                    <div class="icon" v-if="validateStep(step)"><i class="fas fa-check-circle"></i></div>
                                </div>
                            </div>

                            <div class="flex-1 flex items-center text-white font-bold bg-primary px-4 py-2 transition-opacity transition" :class="validateStep() ? 'opacity-1 cursor-pointer' : 'opacity-0'" @click="nextStep()">
                                <div class="">{{ nextText }}</div>
                                <div class="icon ml-2"><i class="fas fa-chevron-right"></i></div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</template>

<script>

    import Feedback from '@/Mixins/Feedback';

    export default {

        props: [],

        mixins: [Feedback],

        data() {
            return {

                url: '',
                transitionDirection: 'inquiry-form-forward',
                currentStep: 1,
                steps: [1,2,3,4],
                types: ['Boarding', 'Day'],

                categories: [],

                form: {
                    name: '',
                    email: '',
                    phone: '',
                    target_grade: '',
                    target_year: '',
                    student_type: '',
                    gender: '',
                },

                grades: [8,9,10,11,12],
            }
        },

        computed: {

            show() {
                return this.$store.state.page.name === 'Inquiry';
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

            nextText() {
                if (this.currentStep === this.steps.length) {
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

            formIsValid() {
                let valid = true;

                let steps = this.steps.slice(0, this.steps.length - 1);

                steps.forEach(step => {
                    if (!this.validateStep(step)) {
                        valid = false;
                    }
                });

                return valid;
            }
        },

        watch: {
        },

        methods: {

            validateStep: function(step) {

                if (!step) {
                    step = this.currentStep;
                }

                if (step === 1) {
                    return this.form.name.length > 2 && this.form.phone.length > 9 && this.validEmail;
                }

                if (step === 2) {
                    return this.form.target_year && this.form.target_grade && this.form.student_type;
                }

                if (step === 3) {
                    return true;
                }

                if (step === 4) {
                    return this.formIsValid;
                }

            },

            goToStep: function(step) {

                if (step < this.currentStep) {
                    this.transitionDirection = 'inquiry-form-backward';
                } else {
                    this.transitionDirection = 'inquiry-form-forward';
                }

                if (this.validateStep(step)) {
                    this.currentStep = step;
                }

            },

            nextStep: function() {

                this.transitionDirection = 'inquiry-form-forward';

                if (this.currentStep < this.steps.length && this.validateStep()) {
                    this.currentStep++;
                } else if (this.currentStep === this.steps.length && this.formIsValid) {
                    this.submit();
                }

            },

            prevStep: function() {
                this.transitionDirection = 'inquiry-form-backward';

                if (this.currentStep > 1) {
                    this.currentStep--;
                }
            },

            submit: function() {

                this.$http.post('/inquiry', this.form).then( response => {
                    this.processSuccess(response);
                }, error => {
                    this.processErrors(error.response);
                });

            }
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
