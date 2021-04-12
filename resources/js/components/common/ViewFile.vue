<template>
    <div
        v-closeEsc
        class="page-overlay"
        @click="closeAssignment"
        @keydown.esc="closeAssignment">
        <div class="page-overlay-wrapper">
        <div class="page-overlay-content">
            <div class="row">
                <div class="col d-none d-lg-block">
                    <div class="page-overlay-header">
                        {{ viewFile.name }}
                        <span v-if="submitted" class="text-success">(Completed)</span>
                    </div>
                </div>
                <div class="col page-overlay-btns">
                    <button
                        v-if="viewId"
                        type="button"
                        class="btn btn-sm btn-primary pl-1"
                        @click.prevent.stop="upload(viewId)">
                        <i class="fas fa-arrow-up mr-1"></i> {{ submitted ? 'Resubmit' : 'Submit Assignment' }}
                    </button>
                    <a

                        v-if="!fileIsLink(viewFile.location)"
                        class="btn btn-sm btn-primary pl-1"
                        :download="sanitizeFileName(viewFile.name)"
                        :href="'/uploads/assignments/' + viewFile.location"
                        target="_blank"
                        @click.stop="">
                        <i class="fas fa-download mr-1"></i> Download
                    </a>
                    <button
                        type="button"
                        class="btn btn-sm btn-primary pl-1"
                        @click="closeAssignment">
                        <i class="fas fa-times mr-1"></i> Close
                    </button>
                </div>
            </div>

            <div
                v-if="fileIsLink(viewFile.location)"
                class="text-center border bg-white"
                :class="[!viewFile.screenshot ? 'gb-assignment-screenshot-empty' : 'p-5']"
                @click.stop="">
                <div v-if="viewFile.screenshot" class="gb-assignment-screenshot">
                    <a v-btooltip="{title:'Opens in new tab'}" :href="viewFile.location" target="_blank"><img :src="'/uploads/assignments/' + viewFile.screenshot" /></a>
                    <div v-if="['teacher', 'assistant-teacher'].includes(user_role)" class="text-right">
                        <a v-btooltip="{title:`Some websites don't allow screenshots to be generated of them automatically. Submit a request for a manual replacement.`}" :href="'/support/screenshot/'+viewFile.id" class="small text-gray">Image malformed?</a>
                    </div>
                </div>
                <a v-btooltip="{title:'Opens in new tab'}" :href="viewFile.location" target="_blank" class="btn btn-success">View Assignment Link <i class="fas fas fa-external-link-alt"></i></a>
            </div>

            <div v-else @click.prevent.stop="">
                <img
                    v-if="fileIsImage(viewFile.location.toLowerCase())"
                    :src="'/uploads/assignments/' + viewFile.location"
                    class="rounded d-block w-auto h-auto mx-auto">

                <div
                    v-else-if="viewFile.location.toLowerCase().indexOf('pdf') !== -1"
                        id="pdf"
                    v-pdfViewer:url="'/uploads/assignments/' + viewFile.location"
                    class="gb-assignment-pdfviewer">
                </div>

                <div v-else class="gb-assignment-docviewer">
                    <iframe
                        :src="'https://view.officeapps.live.com/op/embed.aspx?src=https://edu.inventionlandinstitute.com/uploads/assignments/' + viewFile.location"
                        class="gb-assignment-docviewer"
                        width="100%"
                        height="800px"
                        frameborder="0">
                        This is an embedded <a target="_blank" href="http://office.com">Microsoft Office</a> document, powered by <a target="_blank" href="http://office.com/webapps">Office Online</a>.
                    </iframe>
                </div>
            </div>
        </div>
        </div>
    </div>
</template>
<script>
import { fileNameIsImage } from '../../functions/utils';
import { pdfViewer } from '../../directives/pdfViewer';
import closeEsc from '../../directives/closeEsc';

export default {
    name: 'ViewFile',
    directives: {
        pdfViewer,
        closeEsc
    },
    props: {
        viewFile: {
            type: Object,
            required: true
        },
        viewId: {
            type: Number,
            default: 0
        },
        submitted: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            closed: false
        }
    },

    methods: {
        //
        // Close file
        // --------------------------------------------------------------------------
        closeAssignment() {
            if(this.closed) return;
            this.$emit('close');
            this.closed = true;
        },
        //
        // Show upload
        // --------------------------------------------------------------------------
        upload() {
            this.$emit('upload', this.viewId);
        },
        //
        // Check file type
        // --------------------------------------------------------------------------
        fileIsImage(loc) {
            return fileNameIsImage(loc);
        },
        //
        // Check if link
        // --------------------------------------------------------------------------
        fileIsLink(loc) {
            return loc.toLowerCase().indexOf('http://') !== -1
                || loc.toLowerCase().indexOf('https://') !== -1;
        }
    }
}
</script>
