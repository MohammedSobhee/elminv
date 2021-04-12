import { getFullName, getArrayTotal, getNestedArrayTotal } from './utils';

//
// Set up user list
// --------------------------------------------------------------------------
const setupUserList = (result, userType) => {
    let list = result;

    // Sort user type list by student's last name
    sortUserList(list, userType);

    // Set up user's additional props
    list = list.map(user => setupUser(user, userType));
    return list;
};

//
// Sort user lists
// --------------------------------------------------------------------------
const sortUserList = (list, userType) => {
    userType === 'user' &&
        list.sort((a, b) => {
            return a.last_name.localeCompare(b.last_name);
        });
};

//
// Set up users
// --------------------------------------------------------------------------
const setupUser = (user, userType) => {
    const modUser = user;
    // Add fullname prop to user type list
    userType === 'user' && (modUser.fullname = getFullName(user.first_name, user.last_name));

    // Add team member list for template use
    userType === 'team' &&
        (modUser.members = user.members.map(member =>
            getFullName(member.first_name, member.last_name)
        ));
    return modUser;
};

//
// Get Selected
// --------------------------------------------------------------------------
const getSelectedClassUser = (selectedClass, userID) => {
    return selectedClass[selectedClass.userType + 'List'].find(obj => obj.id === userID);
};

const getSelectedCatProp = (selectedCat, asgmtType) => {
    return asgmtType === 'Activity' ? 'activity' : selectedCat.name.toLowerCase();
};

//
// Return project ID if single project
// --------------------------------------------------------------------------
const preSelectProject = asgmnts => {
    if(!asgmnts.length) return;
    if (asgmnts.length === 1 && typeof asgmnts[0].project_id !== 'undefined') {
        return asgmnts[0].project_id;
    } else if(typeof asgmnts[0].locked !== 'undefined') {
        const asgmt = asgmnts.find(obj => obj.locked);
        return (typeof asgmt !== 'undefined') ? asgmt.project_id : 0;
    }
    return;
};

//
// Update categories' hasAssignments property that determines if it should
// be shown in template
// --------------------------------------------------------------------------
const updateAssignmentsCheck = (cls, userType) => {
    const userList = userType + 'List';
    for (const cat of cls.categories) {
        const category = cat.name.toLowerCase();
        const catAssignments = cls[userList].reduce((total, user) => {
            if(typeof user[category] !== 'undefined')
                return total + user[category].length
        }, 0);
        cat.categoryHasAsgmts = catAssignments > 0 ? cls.id : false
        cat.pendingAsgmts = catPendingAssignments(category, cls[userList]);
    }

    const actAssignments = cls[userList].reduce((total, user) => {
        if(user['activity'] !== undefined)
            return total + user['activity'].length
    }, 0);
    cls.categories.find(obj => obj.name === 'Classwork')
        .categoryHasAsgmts = actAssignments > 0 ? cls.id : false
    return cls.categories;
}

//
// Get pending assignments
// --------------------------------------------------------------------------
const catPendingAssignments = (cat, users) => {
    const pending = asgmts => asgmts.reduce((total, a) => total + isStatus.pending(a), 0);
    let completeTotal = 0;
    users.forEach(u => {
        let userTotal = 0;
        if(u[cat] !== undefined && u[cat].length) {
            userTotal += u[cat].reduce((uTotal, a) => uTotal + isStatus.pending(a), 0);
            if(cat === 'classwork') {
                userTotal += u['activity'].reduce((pTotal, w) => pTotal + (w.locked && pending(w.worksheets)), 0);
            }
        }
        u.pendingAsgmts = userTotal;
        completeTotal += userTotal;
    });
    return completeTotal;
}

//
// Graded / Pending / Messaged filters
// --------------------------------------------------------------------------
const isStatus = {
    graded: obj => obj.grade !== null && !(obj.grade === null && obj.message),
    pending: obj => obj.grade === null && obj.status,
    messaged: obj => obj.message,
    any: obj => obj.grade !== null || obj.status || obj.message,
    all: obj => obj
}

const getCatAsgmtsByStatus = (asgmtArray, status) => {
    return asgmtArray.filter(obj => isStatus[status](obj));
};

const getWorksheetsByStatus = (asgmtArray, status) => {
    const hasWorksheets = (proj, cb) => proj.worksheets.some(obj => cb(obj));
    return asgmtArray
        .filter(proj => hasWorksheets(proj, isStatus[status]))
        .map(proj => {
            const project = proj;
            project.worksheets = project.worksheets.filter(obj => isStatus[status](obj));
            return project;
        });
};

const getAsgmtsByStatus = (usr, cls, status) => {
    const user = usr;
    let totalGrade, totalPoints, totalAGrade, totalAPoints, totalPercentage;
    totalGrade = totalPoints = totalAGrade = totalAPoints = totalPercentage = 0;

    // Grade Tally function
    const tallyGrade = cat => {
        let catValue = cls.userType === 'team' ? 100 : cat.value;

        if (cat.name === 'Classwork') {
            cat.totalGrade += totalAGrade;
            cat.totalPoints += totalAPoints;
        }

        totalGrade += cat.totalGrade;
        totalPoints += cat.totalPoints;

        // Assign 1 for use in weight equation
        //!cat.totalGrade && !cat.totalPoints && (cat.totalGrade = cat.totalPoints = 1);

        // Calculate weight
        cat.totalGradePercentage = (cat.totalGrade / cat.totalPoints) * (catValue / 100);
        cat.totalGradePercentage = +Math.round(100 * cat.totalGradePercentage).toFixed(0) || 0;

        // Assign back to 0 for use in display
        cat.totalGrade === 1 && cat.totalPoints === 1 && (cat.totalGrade = cat.totalPoints = 0);
    }

    // Filter Activity sheets
    if(user['activity'] !== undefined &&  user['activity'].length > 0) {
        user['activity'] = getWorksheetsByStatus(user['activity'], status);
        totalAGrade += getNestedArrayTotal(user['activity'], 'worksheets', 'grade');
        totalAPoints += getNestedArrayTotal(user['activity'], 'worksheets', 'points');//, 'grade');

        // Tally grade for team
        if(cls.userType === 'team') {
            const cat = cls.categories.find(obj => obj.name === 'Classwork');
            cat.totalGrade = totalAGrade;
            cat.totalPoints = totalAPoints;
            tallyGrade(cat);
            totalPercentage = cat.totalGradePercentage;
        }
    }

    // Filter Custom assignments
    for (const cat of cls.categories) {
        const category = cat.name.toLowerCase();
        if(user[category] !== undefined && user[category].length > 0) {
            user[category] = getCatAsgmtsByStatus(user[category], status);
            // Tally up grade
            cat.totalGrade = getArrayTotal(user[category], 'grade');
            cat.totalPoints = getArrayTotal(user[category], 'points');//, 'grade');
            tallyGrade(cat);
        }
    }

    // Tally total for individuals
    if(cls.userType !== 'team') {
        totalPercentage = getArrayTotal(cls.categories, 'totalGradePercentage');
    }

    // For use in display of overall grade for user
    user.totalGrade = totalGrade;
    user.totalPoints = totalPoints;
    user.totalPercentage = totalPercentage;
    return user;
};


export {
    setupUserList,
    preSelectProject,
    getSelectedClassUser,
    getSelectedCatProp,
    updateAssignmentsCheck,
    getAsgmtsByStatus,
    catPendingAssignments
};
