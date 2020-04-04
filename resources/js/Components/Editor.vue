<template>
  
    <div class="relative mb-4 w-full">

        <transition name="text-sm">
            <div class="label" v-if="text && label">
                <label>{{ label }}</label>
            </div>
        </transition>

        <editor-menu-bar :editor="editor" :show-menu="showMenu" v-slot="{ commands, isActive, focused, getMarkAttrs }">
            <div class="relative">
                <transition name="editor-menu-bar">

                    <div v-show="true" class="mt-2 relative z-2">

                        <div class="text-sm flex flex-wrap items-center text-gray-700" :class="showBg ? 'bg-gray-100 border-t border-l border-r' : ''">
                            <div class="menubar__button" :class="{ 'is-active': isActive.bold() }" @click="commands.bold" ><i class="fas fa-bold"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.italic() }" @click="commands.italic" ><i class="fas fa-italic"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.strike() }" @click="commands.strike" ><i class="fas fa-strikethrough"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.underline() }" @click="commands.underline" ><i class="fas fa-underline"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.paragraph() }" @click="commands.paragraph" ><i class="fas fa-paragraph"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.heading({ level: 1 }) }" @click="commands.heading({ level: 1 })" > H1 </div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.heading({ level: 2 }) }" @click="commands.heading({ level: 2 })" > H2 </div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.heading({ level: 3 }) }" @click="commands.heading({ level: 3 })" > H3 </div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.bullet_list() }" @click="commands.bullet_list" ><i class="fas fa-list-ul"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.ordered_list() }" @click="commands.ordered_list" ><i class="fas fa-list-ol"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.blockquote() }" @click="commands.blockquote" ><i class="fas fa-quote-right"></i></div>
                            <div class="menubar__button" :class="{ 'is-active': isActive.link() }" @click="toggleLinkMenu(getMarkAttrs('link'))" ><i class="fas fa-link"></i></div>
                            <div class="menubar__button" v-if="false" :class="{ 'is-active': isActive.toggleClass({ class: 'float-right' }) }" @click="commands.toggleClass({ class: 'float-right' })" ><i class="fas fa-window-restore"></i></div>
                        </div>

                    </div>

                </transition>

            </div>

        </editor-menu-bar>

        <div class="editor" :class="showBg ? 'bg-gray-100 border px-4 py-2' : ''">

            <editor-menu-bubble
                :editor="editor" 
                @hide="hideLinkMenu" 
                v-slot="{ commands, isActive, getMarkAttrs, menu }"
            >

                <transition name="fade">
                    <div class="absolute top-0 w-full z-10 text-gray-700"
                        v-show="linkMenuIsActive"
                        style="background-color: rgba(255,255,255,0.5);"
                    >
                        <div class="relative bg-gray-200 p-4 shadow w-full">
                            <div class="text-lg hover:text-gray-800 absolute top-0 right-0 -mt-2 -mr-2" @click="hideLinkMenu()"><i class="fas fa-times-circle"></i></div>

                            <div class="flex items-center form relative">
                                <input class="remove" type="text" v-model="linkUrl" placeholder="https://www.brentwood.bc.ca/" ref="linkInput" @keyup.esc="hideLinkMenu" @keyup.enter="setLinkUrl(commands.link, linkUrl)" />
                                <div v-if="linkUrl" class="mr-2 text-lg hover:text-gray-800 absolute right-0" @click="setLinkUrl(commands.link, null)"><i class="fas fa-times-circle"></i></div>
                            </div>

                            <page-tree max-height="300px;" 
                                :emit-event="true" 
                                @selected="setLinkUrl(commands.link, $event)" 
                                :show-content-elements="true" 
                                :expanded="false"
                            ></page-tree>

                            <checkbox-input class="mt-2" v-model="linkButton" label="Is A Button"></checkbox-input> 
                            <checkbox-input class="mt-2" v-model="linkNewWindow" label="Open In New Window"></checkbox-input> 
                            <checkbox-input class="mt-2" v-model="linkFloatRight" label="Float Right"></checkbox-input> 

                            <div class="mt-2 button" @click="setLinkUrl(commands.link, linkUrl)">Apply Link</div>
                        </div>

                    </div>
                </transition>

            </editor-menu-bubble>

            <div class="placeholder absolute pointer-events-none z-2" :class="showBg ? '' : 'pt-4'" v-if="!text && (placeholder || label)">{{ placeholder ? placeholder : label }}</div>

            <editor-content class="editor__content" :editor="editor" />

        </div>

    </div>

</template>

<script>

    import { Editor, EditorContent, EditorMenuBar, EditorMenuBubble } from 'tiptap'

    import {
          Blockquote,
          BulletList,
          CodeBlock,
          HardBreak,
          Heading,
          ListItem,
          OrderedList,
          TodoItem,
          TodoList,
          Bold,
          Code,
          Italic,
          //Link,
          Strike,
          Underline,
          History,
    } from 'tiptap-extensions'

    import CustomLink from '@/Components/CustomLink';
    import ToggleClass from '@/Components/ToggleClass';
    import CheckboxInput from '@/Components/CheckboxInput.vue';
    import PageTree from '@/Components/PageTree.vue';

    export default {

        components: {
            EditorContent,
            EditorMenuBar,
            EditorMenuBubble,
            'checkbox-input': CheckboxInput,
            'page-tree': PageTree,
        },

        props: ['value', 'placeholder', 'label', 'focus', 'showBg'],

        data() {
            return {
                editor: new Editor({
                    extensions: [
                        new Blockquote(),
                        new BulletList(),
                        new CodeBlock(),
                        new HardBreak(),
                        new Heading({ levels: [1, 2, 3] }),
                        new ListItem(),
                        new OrderedList(),
                        new TodoItem(),
                        new TodoList(),
                        new Bold(),
                        new Code(),
                        new Italic(),
                        new Strike(),
                        new Underline(),
                        new History(),
                        new CustomLink(),
                        new ToggleClass(),
                    ],
                    onUpdate: _.debounce( ({ getJSON, getHTML }) => {
                        //this.json = getJSON()
                        this.html = getHTML()
                    }, 50),
                }),
                //json: 'JSON',
                html: 'HTML',
                linkUrl: null,
                linkButton: false,
                linkNewWindow: false,
                linkFloatRight: false,
                linkMenuIsActive: false,
                showMenu: false,
            }
        },

        computed: {
            text() {
                var doc = new DOMParser().parseFromString(this.value, 'text/html');
                return doc.body.textContent || "";
            },
            focused() {
                return this.editor.focused;
            },
            linkClasses() {

                let classes = '';

                if (this.linkButton) {
                    classes += 'button ';
                }

                if (this.linkFloatRight) {
                    classes += 'float-right ';
                }

                return classes;
            }
        },

        watch: {

            html() {
                this.$emit('input', this.html);
            },

            value() {
                if (this.value != this.html) {
                    this.editor.setContent(this.value)
                }
            },

            focused: _.debounce( function() {
                this.showMenu = this.focused;
            }, 250),

        },

        mounted() {
            this.editor.setContent(this.value)
            if (this.focus) {
                this.editor.focus()
            }
        },

        methods: {
            toggleLinkMenu(attrs) {
                if (this.linkMenuIsActive) {
                    this.hideLinkMenu();
                } else {
                    this.$eventer.$emit('refresh-page-tree');
                    this.linkUrl = attrs.href
                    this.linkMenuIsActive = true
                    this.$nextTick(() => {
                        this.$refs.linkInput.focus()
                    })
                }
            },

            hideLinkMenu() {
                this.linkUrl = null;
                this.linkButton = false;
                this.linkNewWindow = false;
                this.linkFloatRight = false;
                this.linkMenuIsActive = false;
            },

            setLinkUrl(command, url) {
                command({ 
                    href: url, 
                    target: this.linkNewWindow ? '__blank' : null, 
                    class: this.linkClasses,
                })
                this.hideLinkMenu()
            },
        },

        beforeDestroy() {
            this.editor.destroy()
        },

    }
</script>

<style>

@keyframes editor-menu-bar {
    0% {
        max-height: 0px;
        opacity: 0;
    }
    100%   {
        max-height: 45px;
        opacity: 1;
    }
}

.editor-menu-bar-enter-active {
    animation: editor-menu-bar var(--transition-time) ease-out;
}

.editor-menu-bar-leave-active {
    animation: editor-menu-bar var(--transition-time) reverse;
}

</style>
