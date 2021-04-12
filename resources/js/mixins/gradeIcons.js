export default {
    methods: {
        //
        // Grade icons
        // --------------------------------------------------------------------------
        getIconBackground(assignment) {
            let bg = 'card-icon-gray';

            if (assignment.grade === null) {
                if (assignment.status) bg = 'card-icon-sent';
                else if (assignment.has_answers) bg = 'card-icon-pending';
            } else {
                bg = 'card-icon-graded';
            }
            return bg;
        },

        getIcon(assignment, statusIcon = 'fas fa-ellipsis-h') {
            let icon = 'far fa-file-alt';
            if (assignment.grade === null) {
                if (assignment.status) icon = statusIcon;
                else if (assignment.has_answers) icon = 'fas fa-circle-notch';
            } else {
                icon = 'fas fa-check';
            }
            return icon;
        },
        //
        // Grade title
        // --------------------------------------------------------------------------
        getTitle(assignment) {
            let title = 'Todo';

            if (assignment.grade === null) {
                if (assignment.status === 1) title = 'Assignment has been submitted.';
                else if (assignment.status === 2) title = 'Assignment has been sent back.';
                else if (assignment.has_answers) title = 'Assignment in progress...';
            } else {
                if(this.user_role === 'student') title = 'Assignment has been graded.';
            }
            return title;
        }
    }
};
