<template>
    <div class="manage-assignments assignments">
        <!-- <pre class="small">{{ worksheet }}</pre> -->
        <!-- <pre class="small">{{ custom }}</pre> -->
        <div v-if="viewAssignment && viewAssignment.disabled" class="alert alert-warning" role="alert">
            This content has been disabled for demo accounts.
        </div>
        <grade-assignment
            v-else-if="viewingAssignment"
            v-closeEsc
            type="viewing"
            :assignment="viewingAssignment"
            :project_id="viewingAssignment.project_id"
            class="gb-assignment"
            @close="closeAssignment">
        </grade-assignment>
        <p>Optionally select an assignment and assign due dates for each class.</p>
        <div v-if="class_count">
            <!-- Worksheet Listing -->
            <div v-if="worksheets.length">
                <h4 class="mt-5">Activity Sheets</h4>
                <div v-if="edit.type !== 2" class="accordion">
                    <template v-for="asgmt in worksheets">
                        <div :key="asgmt.id" :class="selectedAssignment(asgmt.id) && 'collapse-selected'" class="card">
                            <div
                                class="card-header align-items-center"
                                @click="selectAssignment(asgmt.id)">
                                <span class="card-icon align-self-start card-icon-gray"><i class="far fa-file-alt"></i></span>
                                {{ asgmt.assignment_name }}
                                <i v-if="asgmt.allDatesSet" v-btooltip="{title: 'All classes have due dates', delay: 100}" class="ml-3 float-right fas fa-check text-light-gray small card-header-icon" aria-hidden="true"></i>
                            </div>
                            <collapse>
                                <div v-show="selectedAssignment(asgmt.id)">
                                    <div class="card-body">
                                        <div class="text-right" style="line-height:1">
                                            <a
                                                href="#"
                                                class="text-primary small"
                                                style="cursor:pointer;position:relative;top:-.75rem;left:0"
                                                @click.prevent="viewAssignment(asgmt.id)">
                                                View Assignment
                                            </a>
                                        </div>
                                        <!-- Classes -->
                                        <due-dates :assignment="asgmt" :type="1" />
                                    </div>
                                </div>
                            </collapse>
                        </div>
                    </template>
                </div>
                <div v-else>
                    <button type="button" class="btn btn-sm btn-primary" @click="resetEditing">Show Activity Sheets</button>
                </div>
            </div>
            <!-- End Worksheet Listing -->

            <!-- Custom listing -->
            <div v-if="customs.length" ref="custom">
                <h4 class="pt-5">Custom Assignments <i v-btooltip="{delay:100,title: 'Only classes that have been assigned to an assignment will be listed. Edit assignments to assign classes.'}" class="medium fas fa-question-circle"></i></h4>
                <div v-if="edit.type !== 1" class="accordion assignments">
                    <template v-for="asgmt in currentCustoms">
                        <div
                            v-if="asgmt.classes.length"
                            :key="asgmt.id"
                            :class="selectedAssignment(asgmt.id) && 'collapse-selected'"
                            class="card">
                            <div
                                class="card-header align-items-center"
                                @click="selectAssignment(asgmt.id, 2)">
                                <span class="card-icon align-self-start card-icon-gray"><i class="far fa-file-alt"></i></span>
                                {{ asgmt.assignment_name }}
                                <span class="text-muted small">({{ asgmt.file_name }})</span>
                                <i v-if="asgmt.allDatesSet" v-btooltip="{title: 'All classes have due dates', delay: 100}" class="ml-3 float-right fas fa-check text-light-gray small card-header-icon" aria-hidden="true"></i>
                            </div>
                            <collapse>
                                <div v-show="selectedAssignment(asgmt.id)">
                                    <div class="card-body">
                                        <div class="text-right" style="line-height:1">
                                            <a
                                                href="#"
                                                class="text-primary small"
                                                style="cursor:pointer;position:relative;top:-.75rem;left:0"
                                                @click.prevent="viewAssignment(asgmt.id, 2)">
                                                View Assignment
                                            </a>
                                            <a
                                                class="text-primary small ml-3"
                                                style="cursor:pointer;position:relative;top:-.75rem;left:0"
                                                :href="`/edit/assignments/${asgmt.id}`">
                                                Edit Assignment
                                            </a>
                                        </div>
                                        <!-- Classes -->
                                        <due-dates :assignment="asgmt" :type="2" />
                                    </div>
                                </div>
                            </collapse>
                        </div>
                    </template>
                </div>
                <div v-else>
                    <button type="button" class="btn btn-sm btn-primary" @click="resetEditing">Show Custom Assignments</button>
                </div>
                <pagination
                    v-show="totalFiles / resultsPerPage > 1"
                    :total="totalFiles"
                    :results-per-page="resultsPerPage"
                    :current-page="currentPage"
                    class="mt-3"
                    @pagechanged="paginateInanimate">
                </pagination>
            </div>
            <info-alert v-else-if="!worksheets.length" class="mt-5">
                Before assigning due dates, start by <a href="/edit/assignments">adding</a> assignments.
            </info-alert>

            <!-- End Custom listing -->
        </div>
        <info-alert v-else class="mt-5">
            Before assigning due dates, start by <a href="/edit/class#modal-classAdd">adding</a> your first class.
        </info-alert>
    </div>
</template>


<script>
import { findObjMoveTop, changeLayout, clearObject } from '../functions/utils';
import { pdfViewer } from '../directives/pdfViewer';
import closeEsc from '../directives/closeEsc';

export default {
    name: 'ManageDueDates',

    components: {
        DueDates: () => import(/* webpackChunkName: 'due-dates' */ './common/DueDates'),
        GradeAssignment: () => import(/* webpackChunkName: 'grade-assignment' */ './gradebook/GradeAssignment')
    },

    directives: {
        pdfViewer,
        closeEsc
    },

    props: {
        class_count: {
            type: Number,
            required: true
        },
        worksheet: {
            type: Array,
            required: true
        },
        custom: {
            type: Array,
            required: true
        },
        editing: {
            type: Object,
            default: () => {}
        }
    },

    data() {
        return {
            testing: true,
            edit: this.editing,
            worksheets: this.worksheet,
            customs: this.custom,
            selectedDueDate: {},
            selected: {},
            viewingAssignment: null,
            currentCustoms: null,
            searchedCustoms: null,
            animate: true,
            currentPage: 1,
            resultsPerPage: 30,
            searchCriteria: {
                term: null,
                category: null,
                class: null,
                status: null
            },
            link: '',
            viewFile: null,
            totalPoints: 0
        };
    },

    computed: {
        isSearching() {
            return Object.values(this.searchCriteria).some(val => val !== null && val !== '');
        },
        totalFiles() {
            return this.isSearching ? this.searchedCustoms.length : this.customs.length;
        }
    },

    watch: {
        searchCriteria: {
            deep: true,
            handler: function(val) {
                this.compileSearch(val);
            }
        }
    },

    created() {
        // Check if linked directly to an assignment
        this.highlightTargetAssignment();

        // Show only the first page of files on created
        this.currentCustoms = this.customs.slice(0, this.resultsPerPage);

    },

    methods: {
        //
        // Highlight targetted assignment
        // --------------------------------------------------------------------------
        highlightTargetAssignment() {
            if(this.edit.id) {
                const asgmts = this.edit.type === 2 ? this.customs : this.worksheets;
                findObjMoveTop(asgmts, this.edit.id, false);
                this.selectAssignment(this.edit.id, this.edit.type);
                setTimeout(() => this.$refs.custom.scrollIntoView(
                    { behavior: 'smooth', block: 'start', inline: 'nearest' }
                ), 50);
            }
        },
        //
        // Determine which assignment selected
        // --------------------------------------------------------------------------
        selectedAssignment(id) {
            return this.selected.id && this.selected.id === id;
        },
        //
        // Select category and add its cookie
        // --------------------------------------------------------------------------
        selectAssignment(id, type = 1) {
            const vm = this;
            if (vm.selected.id === id || id === null) {
                vm.selected = {};
            } else {
                vm.selected = this.getAssignmentByID(id, type);
            }
            clearObject(this.selectedDueDate);
        },

        //
        // Get assignment by ID
        // --------------------------------------------------------------------------
        getAssignmentByID(id, type) {
            const asgmts = type === 2 ? this.customs : this.worksheets;
            return asgmts.find(obj => obj.id === id);
        },

        //
        // Reset Editing
        // --------------------------------------------------------------------------
        resetEditing() {
            this.edit.id = 0;
            this.edit.type = 0;
        },

        //
        // View / Close Assignment
        // --------------------------------------------------------------------------
        viewAssignment(id, type) {
            changeLayout('full');
            this.viewingAssignment = this.getAssignmentByID(id, type);
        },

        closeAssignment() {
            this.viewingAssignment = null;
            changeLayout();
        },

        //
        // Paginate
        // --------------------------------------------------------------------------
        paginate(page) {
            const vm = this;
            vm.currentPage = page;
            --page; // eslint-disable-line no-param-reassign
            if (vm.isSearching) {
                vm.currentCustoms = vm.searchedCustoms.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            } else {
                vm.currentCustoms = vm.customs.slice(
                    page * vm.resultsPerPage,
                    (page + 1) * vm.resultsPerPage
                );
            }
        },
        paginateInanimate(page) {
            this.animate = false;
            this.paginate(page);
            setTimeout(() => (this.animate = true), 20);
        },
        //
        //  Compile Search
        // --------------------------------------------------------------------------
        compileSearch(val) {
            let search = '';
            // Iterate through search criteria keys and build search string
            Object.keys(val).forEach(k => {
                // If search key is an object (class, categories), use 'name' property value
                if (val[k] !== null) {
                    search += typeof val[k] === 'object' ? ' ' + val[k].name : ' ' + val[k];
                }
            });
            this.searchFilesByTerm(search);
        },
        //
        // Search Files
        // --------------------------------------------------------------------------
        searchFilesByTerm(search) {
            const vm = this;
            if (typeof search === 'undefined' || search === '') {
                vm.currentCustoms = vm.customs;
                vm.reset();
                return;
            }

            // Split multiple search terms into array for iteration
            let searchTerms = typeof search === 'string' ? search.split(' ') : search;

            // Search using all lowercase and remove observer data from file data
            searchTerms = searchTerms.map(s => s.toLowerCase());
            let files = JSON.parse(JSON.stringify(vm.customs));

            // Exclude properties that shouldn't be searched
            files = files.map(({ file_location, created, updated, ...obj }) => obj);

            // Reduce classes to array of name values
            // Reduce category object to name of category
            Object.keys(files).forEach(f => {
                files[f].classes = files[f].classes.map(c => c.name);
                files[f].category.name &&
                    (files[f].category = files[f].category.name.toLowerCase());
            });

            // Determine if search terms include ^active/a, ^deactive/a, or ^inactive/a
            let status = null;
            const tests = [
                { status: 1, regex: /(^|\s)[aA]ctiv[ea]/g },
                { status: 0, regex: /(^|\s)[dD]eactiv[ea]/g },
                { status: 0, regex: /(^|\s)[iI]nactiv[ea]/g }
            ];
            // Iterate through tests, if found in searchTerms, remove from searchTerms array
            // since there is no value in files obj to search for and assign its status
            tests.forEach(t => {
                let index;
                if ((index = searchTerms.findIndex(term => t.regex.test(term))) !== -1) {
                    // eslint-disable-line no-cond-assign
                    searchTerms.splice(index, 1);
                    status = t.status;
                }
            });

            // Filter assigned status results
            status !== null && (files = files.filter(obj => obj.status === status));

            // Filter by iterating through search terms
            searchTerms.forEach(
                term =>
                    (files = files.filter(obj =>
                        Object.keys(obj).some(key =>
                            // Determine if values exist for regular term or probable status search
                            String(obj[key]).toLowerCase().includes(term)
                        )
                    ))
            );

            // Get only IDs from results
            let fileIDs = files.map(f => f.id);

            // Assign the results to searchedCustoms (for paginate)
            vm.searchedCustoms = vm.customs.filter(obj => fileIDs.indexOf(obj.id) !== -1);

            vm.paginate(1);
        },

        //
        // Reset Search
        // --------------------------------------------------------------------------
        reset() {
            const vm = this;
            Object.keys(vm.searchCriteria).forEach(k => (vm.searchCriteria[k] = null));
            vm.searchedCustoms = null;
            vm.currentCustoms = vm.customs.slice(0, vm.resultsPerPage);
            vm.paginate(1);
            vm.checkedDeletes = [];
        },
        resetSearchAndSort() {
            const vm = this;
            vm.customs = vm.sortFiles(vm.customs, 'status', 'created');
            vm.reset();
            vm.setTooltipCookie(vm.cookie, 'reset', vm.cookie);
        }
    }
};
</script>

<style lang="scss" scoped>
.card-header {
    font-size: .9rem;
}
</style>
