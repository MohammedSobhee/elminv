import { getFullName } from '../functions/utils';
import apiRequest from '../functions/apiRequest';

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
    // Add user's activity and custom assignment prop
    return modUser;
};

//
// Get PHP Assignment Type
// --------------------------------------------------------------------------
const getPHPAssignmentType = asgmtType => {
    return asgmtType === 'Activity' ? 1 : 2;
};

//
// Get Selected
// --------------------------------------------------------------------------
const getSelected = {
    classUser(selectedClass, userID) {
        return selectedClass[selectedClass.userType + 'List'].find(obj => obj.id === userID);
    },

    catProp(selectedCat, asgmtType) {
        return asgmtType === 'Activity' ? 'activity' : selectedCat.name.toLowerCase();
    }
};

//
// Return project ID if single project
// --------------------------------------------------------------------------
const isSingleProject = asgmnts => {
    if (asgmnts.length === 1 && typeof asgmnts[0].project_id !== 'undefined')
        return asgmnts[0].project_id;
};
const updateAssignmentsCheck = (cls, userType) => {
    const userList = userType + 'List';
    for (const cat of cls.categories) {
        const catAssignments = cls[userList].reduce((total, user) => {
            const category = cat.name.toLowerCase();
            if(user[category] !== undefined)
                return total + user[category].length
        }, 0);
        cat.hasAssignmentsForClass = catAssignments > 0 ? cls.id : false
    }

    const actAssignments = cls[userList].reduce((total, user) => {
        if(user['activity'] !== undefined)
            return total + user['activity'].length
    }, 0);
    cls.categories.find(obj => obj.name === 'Classwork')
        .hasAssignmentsForClass = actAssignments > 0 ? cls.id : false
}

//
// Get users from API
// --------------------------------------------------------------------------

const getUsers = async (classID, userType, teacherID) => {
    return apiRequest(`/api/show/gradebook/${classID}/${teacherID}`, { user_type: userType }).then(result => {
        return setupUserList(result, userType);
    });
};

//
// Get user assignments from API
// --------------------------------------------------------------------------
const getUserAssignments = (catID, classID, userType, asgmntType, userID, teacherID) => {
    //cls.asgmtType =
    const request = {
        assignment_type: getPHPAssignmentType(asgmntType),
        category: catID,
        user_type: userType,
        user_id: userID
    };
    return apiRequest(`/api/show/gradebook/${classID}/${teacherID}`, request);
}

//
// Load assignments from API
// --------------------------------------------------------------------------
const loadAssignments = async (categories, ...args) => {
    const [classID, userType, asgmntType, teacherID] = [...args];
    const wait = ms => new Promise(r => setTimeout(r, ms));

    return getUsers(classID, userType, teacherID).then( users => {

        users.reduce((accUserPromise, user) => {
            return accUserPromise.then(() => {

                // Categories
                categories.reduce((accCatPromise, cat) => {
                    return accCatPromise.then(() => {
                        const category = cat.name.toLowerCase();

                        getUserAssignments(
                            cat.id, classID, userType, asgmntType, user.id, teacherID
                        ).then(userAssignments => {
                            Vue.set(user, category, userAssignments);
                            return user[category];
                        });
                        return cat;
                    });
                }, Promise.resolve());

                // Worksheets
                wait(1000)
                getUserAssignments(
                    2, classID, userType, 'Activity', user.id, teacherID
                ).then(userAssignments => {
                    Vue.set(user, 'activity', userAssignments);
                    return user['activity'];
                });

                return user;
            });
        }, Promise.resolve());

        return users;
    })
}

export {
    setupUserList,
    getPHPAssignmentType,
    getSelected,
    isSingleProject,
    updateAssignmentsCheck
};
