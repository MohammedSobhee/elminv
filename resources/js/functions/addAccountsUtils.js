import { capitalizeWords, isEmail, passwordCheck } from './utils';

const feedback = {
    valid_file: 'Please upload a valid CSV exported from a spreadsheet program.',
    headers: 'The CSV must contain headers in the first row.',
    first_last: 'The CSV must contain at least the first and last name.',
    email_sent: 'Emails sent!',
    missing_fields: 'Missing fields',
    password_note: 'Passwords cannot be contained inside the note.',
    password_invalid: 'Passwords must contain at least one uppercase letter, a number, and be 6 characters or more.',
    dupes: 'Please correct the duplicate username/emails.',
    class_access: `Class doesn't exist.`
};
//
//
// --------------------------------------------------------------------------
const processProps = (key, obj = null) => {
    let nKey = key.replace(/\s|-/g, '_').toLowerCase();
    nKey = /^first/gi.test(nKey) ? 'first_name' : nKey;
    nKey = /^last/gi.test(nKey) ? 'last_name' : nKey;
    nKey = /^pass/gi.test(nKey) ? 'password' : nKey;
    nKey = /^class/gi.test(nKey) && !nKey === 'class_id' ? 'class_id' : nKey;
    nKey = /^role/gi.test(nKey) || /^type/gi.test(nKey) ? 'role' : nKey;
    nKey = /^note/gi.test(nKey) || /^message/gi.test(nKey) ? 'note' : nKey;
    nKey = ['username', 'user', 'user_name', 'e_mail', 'e-mail'].includes(nKey) ? 'email' : nKey;

    if(obj) {
        let val = obj[key];
        delete obj[key];
        obj[nKey] = val;
    }

    return nKey;
};

//
// Check Props Exist
// --------------------------------------------------------------------------
const checkPropsExist = array => {
    return {
        passwords: array.some(k => /^pass/gi.test(k.key)),
        classes: array.some(k => /^class/gi.test(k.key)),
        roles: array.some(k => /^role/gi.test(k.key) || /^type/gi.test(k.key)),
        notes: array.some(k => /^note|message/gi.test(k.key))
    };
};

//
// Check Values Exist
// --------------------------------------------------------------------------
const checkValuesExist = array => {
    return {
        passwords: array.every(u => u.password),
        codes: array.some(u => u.code),
        roles: array.some(u => u.role),
        notes: array.some(u => u.note),
        //classes: array.some(u => u.class_id),
        classes: array.some(u => {
            if(typeof u.role !== 'undefined')
                return ['student','assistant-teacher'].includes(u.role.slug)
            else
                return u.class_id
        }),
        emails: array.some(u => isEmail(u.email))
    };
};


//
// Create header object
// --------------------------------------------------------------------------
const createHeaders = (hdrs, format = false) => {
    let headers = hdrs.map(h => {
        let header = h;
        let key = processProps(h);
        header = { key: key, label: h };
        return header;
    });
    if(format) {
        headers.forEach(h => {
            h.label = capitalizeWords(h.label.replace('_', ' '));
            // TODO: Quick fix, but improve efficiency,
            h.label = h.label.replace('Class Id', 'Class');
        });
        // Process complete:
        headers = headers.filter(obj => !['id', 'user_id', 'error', 'corrected'].includes(obj.key));
        if (!headers.find(obj => obj.key === 'date')) headers.push({ key: 'date', label: 'Date' });
    }
    return headers;
};

//
// Process Headers
// --------------------------------------------------------------------------
const processHeaders = (arr, classID, classes) => {
    const requiredHeaders = ['first', 'last', 'firstname', 'lastname', 'first name', 'last name', 'first_name', 'last_name'];
    const checkForUsernames = ['username', 'e mail', 'e-mail', 'email', 'user']
    let data = {
        headers: [],
        message: ''
    }

    // Has some headers
    if(!arr.some(val => requiredHeaders.includes(val.toLowerCase()))) {
        data.message = feedback.headers;
        return data;
    }

    // Has at least first and last name
    if(arr.filter(val => requiredHeaders.includes(val.toLowerCase())).length < 2) {
        data.message = feedback.first_last;
        return data;
    }

    data.headers = arr;

    // Check if class_id prop is supplied
    if(classes.length && !data.headers.find(h => /^class/gi.test(h))) {
        data.headers.push('Class');
    }

    // Add Username if not supplied
    if(!data.headers.some(val => checkForUsernames.includes(val.toLowerCase()))) {
        data.headers.push('Username');
    }

    // Add Role if not supplied
    if (!data.headers.find(h => /^role|type/gi.test(h))) {
        data.headers.unshift('Role');
    }

    data.headers = createHeaders(data.headers);
    return data;
};


//
// Prep Uploaded User Objects
// --------------------------------------------------------------------------
const processUploadedUserData = (data, classID, roles, classes) => {

    const getClass = id => {
        let cls = classes.find(obj => obj.id === +id);
        return typeof cls !== 'undefined' ? cls : '';
    }

    const users = data.map((d, i) => {

        let user = d;

        // Create valid object keys
        Object.keys(user).map(key => {
            processProps(key, user);
        });

        // Convert roles
        const defaultRole = roles.find(obj => obj.id === 1);

        if (!user.role) { // Default to student
            user = {
                role: defaultRole,
                ...user
            };
        } else if (typeof user.role !== 'object') { // Find role object
            const userRole = roles.find(obj => obj.id === +user.role);
            user.role = !userRole ? defaultRole : userRole;
        }

        // Add fake id and error keys
        user.id = i + 1;
        user.error = [];

        // Add class_id key
        if(classID) {
            user.class_id = getClass(classID)
        } else if(classes.length) {
            user.class_id = getClass(user.class_id);
        } else {
            user.class_id = '';
        }

        return user;
    });

    return users;
};

//
// Process existing user data
// --------------------------------------------------------------------------
const processExistingData = (users, classes) => {
    let has = checkValuesExist(users);
    has.passwords = users.some(obj => obj.password !== null);

    let headers = createHeaders(Object.keys(users[0]), true);
    const mod = headers.find(obj => obj.key === 'email');
    mod.label = 'Username';

    if(!has.emails) {
        headers = headers.filter(obj => obj.key !== 'sent');
    }

    !has.notes && (headers = headers.filter(obj => obj.key !== 'note'));
    !has.classes && (headers = headers.filter(obj => obj.key !== 'class_id'));
    has.codes && !has.passwords && (headers = headers.filter(obj => obj.key !== 'password'));
    has.passwords && !has.codes && (headers = headers.filter(obj => obj.key !== 'code'));

    users.forEach(u => {
        if (!u.date) u.date = new Date();

        if (!has.emails) delete u.sent;

        if (!has.notes) delete u.note;
        if (has.classes) {
            const cls = classes.find(obj => obj.id === +u.class_id);
            cls && (u.class_id = cls);
        } else {
            delete u.class_id;
        }
        if (has.codes && !has.passwords) delete u.password;
        if (has.passwords && !has.codes) delete u.code;
        if (u.password) u.password = '**********';
    });

    return {
        users: users,
        headers: headers,
        props: {
            emails: has.emails,
            notes: has.notes,
            codes: has.codes,
            roles: has.roles,
            passwords: has.passwords,
            classes: has.classes
        }
    };
}

//
// Get dupes
// https://stackoverflow.com/a/50840474
// --------------------------------------------------------------------------
const checkDupes = data => {
    return Object.values(data.reduce((c, v) => {
        let k = v.email;
        c[k] = c[k] || [];
        k.length && c[k].push(v);
        return c;
    }, {})).reduce((c, v) => {
        if (v.length > 1) {
            v.map(obj => {
                obj.error.push('email');
                return obj;
            });
            return c.concat(v);
        } else {
            v.map(obj => {
                obj.error = obj.error.filter(str => str !== 'email');
                return obj;
            });
            return c;
        }
    }, []);
}


// Checks
// --------------------------------------------------------------------------
const validate = (data => {
    return {
        messages: [],
        dupeData: [],
        exists: checkValuesExist(data),

        // Check Dupes
        dupes: function() {
            this.dupeData = checkDupes(data);
            this.checkErrors('email') && this.setMessage(feedback.dupes);
            let newData = data.filter(obj => !obj.error.includes('email'));
            this.dupeData.length && newData.unshift(...this.dupeData);
            return newData;
        },

        // Check if password is contained in note
        passwordInNote: function() {
            if (!this.exists.notes) return;
            data.forEach(
                user =>
                    user.password &&
                    user.note.indexOf(user.password) !== -1 &&
                    user.error.push('note')
            );
            this.checkErrors('note') && this.setMessage(feedback.password_note);
        },

        // Check if password contains at least one uppercase letter, a number, and be 6 characters or more.
        passwordValidate: function() {
            data.forEach(user => {
                if(user.password && user.password !== '**********') {
                    !passwordCheck.validate(user.password) &&
                    user.error.push('password')
                }
            });
            this.checkErrors('password') && this.setMessage(feedback.password_invalid);
        },

        // Check if user has access to supplied class ID
        classAccess: function(classes) {
            if (!this.exists.classes) return;
            data.forEach(d => {
                let user = d;
                if (
                    user.class_id &&
                    typeof user.role !== 'undefined' &&
                    ['student','assistant-teacher'].includes(user.role.slug)
                ) {
                    const id = typeof user.class_id === 'object' ? user.class_id.id : user.class_id;
                    const cls = classes.find(c => c.id === +id);
                    user.class_name = cls ? cls.class_name : '';
                    user.class_name || user.error.push('class_id');
                }

            });
            this.checkErrors('class_id') && this.setMessage(feedback.class_access);
        },

        // Set error message
        setMessage: function(msg) {
            this.messages.push(msg);
        },

        // Get error messages
        getMessages: function() {
            return this.messages;
        },

        // Check for errors in data array
        checkErrors: (prop = null) =>
            data.some(obj => (prop ? obj.error.includes(prop) : obj.error.length)),

        // Run all and unshift errors
        runAll: function(classes) {
            this.dupes();
            this.passwordInNote();
            this.passwordValidate();
            this.classAccess(classes);
            let newData = data;
            if (this.checkErrors()) {
                const errorData = data.filter(
                    obj => obj.error.length && !obj.error.includes('email')
                );
                newData = data.filter(obj => !obj.error.length);
                this.dupeData.length && newData.unshift(...this.dupeData);
                errorData.length && newData.unshift(...errorData);
            }
            return newData;
        }
    };
});

//
// Input Check
// --------------------------------------------------------------------------
const inputCheck = (correctedData, updatedData, classes, args) => {
    let [userID, index, prop] = [...args];
    const corrected = correctedData.find(obj => obj.id === userID);
    const errored = updatedData.find(obj => obj.id === userID);
    errored[prop] = corrected[prop];
    errored.error = errored.error.filter(err => err !== prop);
    errored.corrected = index;
    // TODO: Figure out a way not to check each to maintain messages
    const validation = validate(updatedData);
    // eslint-disable-next-line no-param-reassign
    updatedData = validation.runAll(classes);
    return validation.getMessages();
}

export {
    processProps,
    processUploadedUserData,
    processExistingData,
    processHeaders,
    createHeaders,
    checkPropsExist,
    checkValuesExist,
    checkDupes,
    inputCheck,
    feedback,
    validate
};
