import { changeLayout, formatDate } from '../functions/utils';
import apiRequest from '../functions/apiRequest';

export default {
    components: {
        ModalUpload: () =>
            import(/* webpackChunkName:"modal-upload" */ '../components/common/ModalUpload')
    },

    data() {
        return {
            selectedCategory: {},
            selectedAsgmt: {},
            notice: {},
            responseMessage: '',
            //submitComplete: '',
            uploadPercentage: 0,
            viewFile: null,
            viewId: 0,
            dragging: false,
            files: null,
            formdata: null
        };
    },

    methods: {
        //
        // View / Close Assignment / Download
        // --------------------------------------------------------------------------
        viewAssignment(assignment, type = 'student') {
            this.viewId = assignment.id;
            this.viewFile = {
                id: assignment.id,
                name: assignment.label,
                location: type === 'student' ? assignment.student_file_location : assignment.teacher_file_location,
                screenshot: assignment.type == 2 ? assignment.file_screenshot : ''
            }
            changeLayout('full');
        },
        closeAssignment() {
            if(this.assignmentId) {
                window.location = document.referrer;
            } else {
                changeLayout();
                this.viewFile = null;
                this.viewId = 0;
            }
        },
        //
        // Check if category has assignments
        // --------------------------------------------------------------------------
        hasAssignments(category) {
            return category.assignments.length > 0;
        },
        //
        // Format Date
        // --------------------------------------------------------------------------
        formatDate(date, type) {
            return formatDate(date, type);
        },
        //
        // Show Upload Modal
        // --------------------------------------------------------------------------
        showUpload(aid) {
            const vm = this;
            vm.formdata = null;
            vm.notice = {};
            if (vm.$options.name === 'AssignmentsCustom') {
                vm.selectedAsgmt = vm.categories
                    .find(obj => obj.id === vm.selectedCategory.id)
                    .assignments.find(obj => obj.id === aid);
            } else {
                vm.selectedAsgmt = vm.assignments.find(obj => obj.id === aid);
            }
            $('#upload' + aid + 'Modal').modal('show');
        },

        //
        // Submit Assignment
        // --------------------------------------------------------------------------
        submit(emit) {
            const vm = this;
            const id = emit.id;
            const submittedID = vm.getSubmittedID(id);
            const comment = this.getComment(id);
            !vm.formdata && (vm.formdata = new FormData());

            // Check if nothing has been submitted
            // if(!vm.formdata.get('file') && comment === null) {
            //     this.setNotice('Forgetting something?', 'danger');
            //     return;

            // Check for file if not type link or resubmit
            if (!vm.formdata.get('file') && vm.selectedAsgmt.type === 1 && !submittedID) {
                vm.setNotice('Upload your assignment file before submitting.', 'danger');

                // Build formdata request
            } else {
                vm.formdata.append('user_id', vm.user_id);
                vm.formdata.append('type', 2);
                vm.formdata.append('type_id', id);
                vm.formdata.append('project_id', id);
                vm.formdata.append('comments', comment);
                vm.formdata.append('file_label', vm.selectedAsgmt.label);
                submittedID && vm.formdata.append('assignment_submitted_id', submittedID);
                vm.postData(vm.formdata);
            }
        },

        //
        // Process Files
        // --------------------------------------------------------------------------
        getFiles(event) {
            const vm = this;
            const input = event.dataTransfer || event.target;
            if (input) {
                !vm.formdata && (vm.formdata = new FormData());

                if (typeof input !== 'undefined' && typeof input.files[0] !== 'undefined') {
                    vm.formdata.append('file', input.files[0]);
                    vm.hasFiles = true;
                }
                vm.setNotice('File added.');
            }
        },

        //
        // Post data
        // --------------------------------------------------------------------------
        postData(data) {
            const vm = this;
            const postURL = '/submit/assignment';
            const asgmtID = +data.get('type_id');
            const submittedID = vm.getSubmittedID(asgmtID);

            apiRequest(postURL, data, {
                onUploadProgress: event => {
                    vm.uploadPercentage = parseInt(
                        Math.round((event.loaded * 100) / event.total),
                        10
                    );
                }
            }).then(response => {
                // Deleted message notice
                if (response.success.indexOf('deleted') !== -1) {
                    vm.setNotice(response.success);
                    // Other actions
                } else if (response.success) {
                    const notice =
                        !submittedID && (vm.hasFiles || vm.selectedAsgmt.type === 2)
                            ? response.success
                            : 'Comment has been saved.';

                    vm.setNotice(notice);
                    vm.updateAssignments(submittedID, asgmtID, response);
                    vm.hasFiles = false;
                } else if (response.error) {
                    vm.responseMessage = response.error;
                }
                setTimeout(() => $('#upload' + asgmtID + 'Modal').modal('hide'), 1000);
            });
        },

        //
        // Get SubmittedID
        // --------------------------------------------------------------------------
        getSubmittedID(asgmtID) {
            const vm = this;
            if (this.$options.name === 'AssignmentsCustom') {
                return vm.categories
                    .find(obj => obj.id === vm.selectedCategory.id)
                    .assignments.find(obj => obj.id === asgmtID).assignment_submitted_id;
            } else {
                return vm.assignments.find(obj => obj.id === asgmtID).assignment_submitted_id;
            }
        },

        //
        // Get Comment
        // --------------------------------------------------------------------------
        getComment(asgmtID) {
            const vm = this;
            if (this.$options.name === 'AssignmentsCustom') {
                return vm.categories
                    .find(obj => obj.id === vm.selectedCategory.id)
                    .assignments.find(obj => obj.id === asgmtID).comments;
            } else {
                return vm.assignments.find(obj => obj.id === asgmtID).comments;
            }
        },
        //
        // Update Assignments
        // --------------------------------------------------------------------------
        updateAssignments(submittedID, asgmtID, response) {
            const vm = this;
            if (this.$options.name === 'AssignmentsCustom') {
                // Assignments
                vm.categories = vm.categories.map(c => {
                    const cat = c;
                    if (cat.id === vm.selectedCategory.id) {
                        cat.assignments.map(a => {
                            const asgmt = a;
                            if (asgmt.id === asgmtID) {
                                asgmt.student_file_location = response.student_file_location;
                                if (response.assignment_submitted_id) {
                                    asgmt.assignment_submitted_id =
                                        response.assignment_submitted_id;
                                }
                                asgmt.status = submittedID ? 2 : 1;
                            }
                            return asgmt;
                        });
                    }
                    return cat;
                });
            } else {
                // Dashboard
                vm.assignments = vm.assignments.map(a => {
                    const asgmt = a;
                    if (asgmt.id === asgmtID) {
                        asgmt.student_file_location = response.student_file_location;
                        if (response.assignment_submitted_id)
                            asgmt.assignment_submitted_id = response.assignment_submitted_id;
                        asgmt.status = submittedID ? 2 : 1;
                        //asgmt.hide = true;
                    }
                    return asgmt;
                });
            }
            // ViewFile update
            if(vm.viewFile && response.student_file_location) {
                vm.viewFile.location = response.student_file_location;
                if(response.type == 2) {
                    vm.viewFile.screenshot = response.student_file_location;
                }
            }
        },

        //
        // Set notice
        // --------------------------------------------------------------------------
        setNotice(str, type = 'success') {
            this.notice = {
                type: type,
                message: str
            };
            //setTimeout(() => (this.notice = {type:null, message:null}), 8000);
        },

        //
        // Default click action
        // --------------------------------------------------------------------------
        defaultAction(assignment) {
            if (assignment.student_file_location) {
                this.viewAssignment(assignment, 'student');
            } else if (assignment.type == 1) {
                this.viewAssignment(assignment, 'teacher');
            } else if (assignment.type == 2) {
                this.viewAssignment(assignment, 'teacher');
            }
        }
    }
};
