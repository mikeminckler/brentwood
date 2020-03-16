<template>
  
    <div class="relative mb-4 w-full">

        <transition name="text-sm">
            <div class="label" v-if="text">
                <label>{{ label ? label : placeholder }}</label>
            </div>
        </transition>

        <editor-menu-bar :editor="editor" :show-menu="showMenu" v-slot="{ commands, isActive, focused, getMarkAttrs }">
            <div>
                <transition name="editor-menu-bar">

                    <div v-show="showMenu">
                        <div class="rounded-t text-sm flex bg-gray-100 p-1 border-t border-l border-r items-center" >
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
                        </div>

                    </div>

                </transition>

            </div>

        </editor-menu-bar>


        <div class="relative bg-gray-100 border px-4 py-2">

            <editor-menu-bubble class="absolute z-10 h-full w-full bg-gray-100 top-0 left-0 rounded" :editor="editor" @hide="hideLinkMenu" v-slot="{ commands, isActive, getMarkAttrs, menu }">

                <transition name="fade">
                    <div class="w-full flex items-center text-sm" v-show="linkMenuIsActive">
                        <div class="relative flex flex-1 items-center">
                            <input class="" type="text" v-model="linkUrl" placeholder="https://" ref="linkInput" @keyup.esc="hideLinkMenu" @keyup.enter="setLinkUrl(commands.link, linkUrl)" />
                            <transition name="fade">
                                <div v-if="linkUrl" class="icon text-lg text-gray-500 absolute right-0" @click="setLinkUrl(commands.link, null)"><i class="fas fa-times-circle"></i></div>
                            </transition>
                        </div>
                        <div class="whitespace-no-wrap bg-primary-500 text-white font-semibold py-1 px-2 rounded-r cursor-pointer" @click="setLinkUrl(commands.link, linkUrl)">Apply Link</div>
                        <div class="icon text-lg hover:text-gray-800" @click="hideLinkMenu"><i class="fas fa-times-circle"></i></div>
                    </div>
                </transition>

            </editor-menu-bubble>

            <div class="placeholder absolute pointer-events-none" v-if="!text && (placeholder || label)">{{ placeholder ? placeholder : label }}</div>

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
          Link,
          Strike,
          Underline,
          History,
    } from 'tiptap-extensions'

    export default {

        components: {
            EditorContent,
            EditorMenuBar,
            EditorMenuBubble,
        },

        props: ['value', 'placeholder', 'label', 'focus'],

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
                        new Link(),
                        new Bold(),
                        new Code(),
                        new Italic(),
                        new Strike(),
                        new Underline(),
                        new History(),
                    ],
                    onUpdate: _.debounce( ({ getJSON, getHTML }) => {
                        //this.json = getJSON()
                        this.html = getHTML()
                    }, 100),
                }),
                //json: 'JSON',
                html: 'HTML',
                linkUrl: null,
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
                    this.linkUrl = attrs.href
                    this.linkMenuIsActive = true
                    this.$nextTick(() => {
                        this.$refs.linkInput.focus()
                    })
                }
            },

            hideLinkMenu() {
                this.linkUrl = null
                this.linkMenuIsActive = false
            },

            setLinkUrl(command, url) {
            command({ href: url })
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
