<template>
<div class="position-relative">
    <div class="editor">
        <editor-icons />
        <editor-menu-bar v-slot="{ commands, isActive, getMarkAttrs, focused }" :editor="editor">
            <div
                class="menubar"
                :class="{ 'is-focused': focused }">
                <button
                    class="menubar__button"
                    title="Bold"
                    :class="{ 'is-active': isActive.bold() }"
                    @click="commands.bold">
                    <icon name="bold" />
                </button>

                <button
                    class="menubar__button"
                    title="Italic"
                    :class="{ 'is-active': isActive.italic() }"
                    @click="commands.italic">
                    <icon name="italic" />
                </button>

                <button
                    class="menubar__button"
                    title="Strikethrough"
                    :class="{ 'is-active': isActive.strike() }"
                    @click="commands.strike">
                    <icon name="strike" />
                </button>

                <button
                    class="menubar__button"
                    title="Underline"
                    :class="{ 'is-active': isActive.underline() }"
                    @click="commands.underline">
                    <icon name="underline" />
                </button>

                <!-- <button
                class="menubar__button"
                :class="{ 'is-active': isActive.code() }"
                @click="commands.code">
                <icon name="code" />
                </button> -->
        <!--
                <button
                class="menubar__button"
                :class="{ 'is-active': isActive.paragraph() }"
                @click="commands.paragraph">
                <icon name="paragraph" />
                </button> -->

                <!-- <button
                class="menubar__button"
                :class="{ 'is-active': isActive.heading({ level: 1 }) }"
                @click="commands.heading({ level: 1 })">
                H1
                </button> -->

                <button
                    v-if="!simple"
                    class="menubar__button"
                    title="Heading 2"
                    :class="{ 'is-active': isActive.heading({ level: 2 }) }"
                    @click="commands.heading({ level: 2 })">
                    H2
                </button>

                <button
                    v-if="!simple"
                    class="menubar__button"
                    title="Heading 3"
                    :class="{ 'is-active': isActive.heading({ level: 3 }) }"
                    @click="commands.heading({ level: 3 })">
                    H3
                </button>

                <button
                    v-if="!simple"
                    class="menubar__button"
                    :class="{ 'is-active': isActive.bullet_list() }"
                    title="Bulleted list"
                    @click="commands.bullet_list">
                    <icon name="ul" />
                </button>

                <button
                    v-if="!simple"
                    class="menubar__button"
                    :class="{ 'is-active': isActive.ordered_list() }"
                    title="Numbered list"
                    @click="commands.ordered_list">
                    <icon name="ol" />
                </button>

                <button
                    v-if="!simple"
                    class="menubar__button"
                    :class="{ 'is-active': isActive.blockquote() }"
                    title="Block quote"
                    @click="commands.blockquote">
                    <icon name="quote" />
                </button>

                <button
                    class="menubar__button"
                    :class="{ 'is-active': isActive.link() }"
                    title="Select text, click this to add link and press enter to insert."
                    @click="showLinkMenu(getMarkAttrs('link'))">
                <icon name="link" />
                </button>

                <!-- <button
                class="menubar__button"
                :class="{ 'is-active': isActive.code_block() }"
                @click="commands.code_block">
                <icon name="code" />
                </button> -->
                <button
                    v-if="!simple"
                    class="menubar__button"
                    title="Create table"
                    @click="commands.createTable({rowsCount: 3, colsCount: 3, withHeaderRow: false })">
                    <icon name="table" />
                </button>
                <span v-if="isActive.table()">
                    <button
                        class="menubar__button"
                        title="Delete table"
                        @click="commands.deleteTable">
                        <icon name="delete_table" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Add column before"
                        @click="commands.addColumnBefore">
                        <icon name="add_col_before" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Add column after"
                        @click="commands.addColumnAfter">
                        <icon name="add_col_after" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Delete column"
                        @click="commands.deleteColumn">
                        <icon name="delete_col" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Add row before"
                        @click="commands.addRowBefore">
                        <icon name="add_row_before" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Add row after"
                        @click="commands.addRowAfter">
                        <icon name="add_row_after" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Delete row"
                        @click="commands.deleteRow">
                        <icon name="delete_row" />
                    </button>
                    <button
                        class="menubar__button"
                        title="Merge cells"
                        @click="commands.toggleCellMerge">
                        <icon name="combine_cells" />
                    </button>
                </span>
            </div>
        </editor-menu-bar>

        <editor-menu-bubble
            v-slot="{ commands, isActive, getMarkAttrs, menu }"
            :editor="editor"
            @hide="hideLinkMenu">
            <div
                class="menububble"
                :class="{ 'is-active': menu.isActive && linkMenuIsActive }"
                :style="`left: ${menu.left}px; bottom: ${menu.bottom}px;`">
                <form v-show="linkMenuIsActive" class="menububble__form" @submit.prevent="setLinkUrl(commands.link, linkUrl)">
                    <input
                        ref="linkInput"
                        v-model="linkUrl"
                        class="menububble__input"
                        type="text"
                        placeholder="https://"
                        @keydown.esc="hideLinkMenu" />
                    <button class="menububble__button" type="button" @click="setLinkUrl(commands.link, null)">
                        <icon name="remove" />
                    </button>
                </form>
                <!-- <template v-else>
                    <button
                        class="menububble__button"
                        :class="{ 'is-active': isActive.link() }"
                        @click="showLinkMenu(getMarkAttrs('link'))">
                        <span>{{ isActive.link() ? 'Update Link' : 'Add Link' }}</span>
                        <icon icon="link" />
                    </button>
                </template> -->
            </div>
        </editor-menu-bubble>
        <editor-content class="editor__content" :editor="editor" />
        <div class="text-right position-relative">
            <div class="editor__buttons">
                <button v-if="(content && content !== '<p></p>') && !disableClear" class="btn btn-sm btn-light editor__button" @click="clearContent">Clear</button>
                <button
                    class="btn btn-sm editor__button btn-light"
                    type="button"
                    @click="submitContent">
                    {{ saved ? saveButtonText[1] : saveButtonText[0] }}
                </button>
            </div>
        </div>
    </div>
    <transition
        name="message"
        enter-active-class="animated bounceInLeft"
        leave-active-class="animated fadeOut">
        <div
            v-if="displayNotice"
                class="text-success text-right editor-notice">
                {{ displayNotice }}
        </div>
    </transition>
</div>
</template>

<script>
import Icon from './EditorWysiwygIcon';
import { Editor, EditorContent, EditorMenuBar, EditorMenuBubble } from 'tiptap';
import { WindowNewLink } from '../../tiptap/WindowNewLink';
import {
    Blockquote,
    BulletList,
    // CodeBlock,
    // Code,
    HardBreak,
    Heading,
    ListItem,
    OrderedList,
    TodoItem,
    TodoList,
    Bold,
    Italic,
    //Link,
    Strike,
    Table,
    TableHeader,
    TableCell,
    TableRow,
    Underline,
    History
} from 'tiptap-extensions';

export default {
    name: 'EditorWysiwyg',
    components: {
        EditorContent,
        EditorMenuBar,
        EditorMenuBubble,
        Icon,
        EditorIcons: () => import(/* webpackChunkName:"editor-icons" */ './EditorIcons')
    },
    props: {
        eid: {
            type: Number,
            default: 0
        },
        name: {
            type: String,
            default: ''
        },
        message: {
            type: String,
            default: ''
        },
        notice: {
            type: Boolean,
            default: false
        },
        simple: {
            type: Boolean,
            default: false
        },
        clear: {
            type: Boolean,
            default: false
        },
        disableClear: {
            type: Boolean,
            default: false
        },
        saveButtonText: {
            type: Array,
            default: () => ['Save', 'Saved']
        }
    },
    data() {
        return {
            linkUrl: null,
            content: this.message,
            linkMenuIsActive: false,
            saved: false,
            displayNotice: '',
            editor: new Editor({
                extensions: [
                    new Blockquote(),
                    new BulletList(),
                    // new CodeBlock(),
                    // new Code(),
                    new HardBreak(),
                    new Heading({ levels: [2, 3] }),
                    new ListItem(),
                    new OrderedList(),
                    new TodoItem(),
                    new TodoList(),
                    // new Link(),
                    new WindowNewLink(),
                    new Bold(),
                    new Italic(),
                    new Strike(),
                    new Table({
                        resizable: true
                    }),
                    new TableHeader(),
                    new TableCell(),
                    new TableRow(),
                    new Underline(),
                    new History()
                ],
                content: this.message,
                onUpdate: () => {
                    this.saved = false;
                }
            })
        };
    },
    watch: {
        notice() {
            if(this.notice) {
                this.displayNotice = this.editor.getHTML() === '<p></p>'
                    ? 'Removed.' : 'Saved.';
                setTimeout(() => (this.displayNotice = ''), 5000);
            }
        },

        clear(val) {
            if(val) {
                this.editor.clearContent();
                this.saved = false;
            }
        }
    },

    beforeDestroy() {
        this.editor.destroy();
    },

    methods: {
        showLinkMenu(attrs) {
            this.linkUrl = attrs.href;
            this.linkMenuIsActive = true;
            const linkInput = this.$refs.linkInput;
            linkInput.focus();
        },

        hideLinkMenu() {
            this.linkUrl = '';
            this.linkMenuIsActive = false;
        },

        setLinkUrl(command, url) {
            command({ href: url });
            this.hideLinkMenu();
            this.editor.focus();
        },
        clearContent() {
            this.editor.clearContent();
            this.submitContent();
        },
        submitContent() {
            const vm = this;
            let message;

            vm.saved = true;

            if(vm.eid) {
                message = {
                    id: vm.eid,
                    content: vm.editor.getHTML()
                }
                if(vm.name) {
                    message.name = vm.name
                }
            } else {
                message = vm.editor.getHTML();
            }

            vm.content = message;
            vm.$emit('saved', message);
        }
    }
};
</script>
