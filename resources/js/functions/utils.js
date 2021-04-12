import lg from './logging';

//
// Wait until element exists
// --------------------------------------------------------------------------
export async function elementExists(selector) {
    while(!document.querySelector(selector)) {
        await new Promise(res => requestAnimationFrame(res))
    }
    return document.querySelector(selector)
}

//
// Layout
// --------------------------------------------------------------------------
export function changeLayout(type) {
    let display = 'block',
        cls = 'col-lg-',
        size1 = '12',
        size2 = '9';
    if(type === 'full') {
        display = 'none';
        size1 = '9';
        size2 = '12'
    }
    document.querySelector('.sidebar') && (document.querySelector('.sidebar').style.display = display);
    document.querySelector('.footer').style.display = display;
    document.querySelector('.content').classList.replace(cls + size1, cls + size2);
    //window.scroll({ top: 0, left: 0, behavior: 'smooth' });
}

//
// Get Full name
// --------------------------------------------------------------------------
export function getFullName(first, last, reverse = true) {
    const fn = reverse ? `${last}, ${first}` : `${first} ${last}`;
    return fn
        .split(' ')
        .map(s => s.charAt(0).toUpperCase() + s.substring(1))
        .join(' ');
}

//
// Capitalize String
// --------------------------------------------------------------------------
export function capitalizeString(value) {
    if (!value) return '';
    return (
        value
            .toString()
            .charAt(0)
            .toUpperCase() + value.slice(1)
    );
}

export function capitalizeWords(value) {
    return value.toLowerCase().replace(/(?:^|\s|["'([{])+\S/g, match => match.toUpperCase());
}

//
// is Email
// --------------------------------------------------------------------------
// https://emailregex.com
export function isEmail(str) {
    return /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/.test(str);
}

//
// Parse MySQL Date Time
// --------------------------------------------------------------------------
function parseMySQLDateTime(date) {
    // https://itnext.io/create-date-from-mysql-datetime-format-in-javascript-912111d57599
    let dateTime = date.split(/[- :]/); // regular expression split that creates array with: year, month, day, hour, minutes, seconds values
    dateTime[1]--; // monthIndex begins with 0 for January and ends with 11 for December so we need to decrement by one
    return  new Date(...dateTime);
}

//
// Get difference in days
// --------------------------------------------------------------------------
export function getDifferenceInDays(date) {
    const d = new Date(date);
    return diffDays(d);
}

function diffDays(d) {
    const c = new Date();
    const diffTime = d.getTime() - c.getTime();
    return Math.round(diffTime / (1000 * 3600 * 24));
}


//
// Get date in common format to USA, humans, etc
// --------------------------------------------------------------------------
function getUSADate(d, type) {
    const month = d.toLocaleString('default', { month: 'long' });
    let dt = `${d.getMonth() + 1}/${d.getDate()}/${d.getFullYear()}`;

    switch(type) {
        case 'long':
            dt = `${month} ${d.getDate()}, ${d.getFullYear()}`;
            break;
        case 'diffDays': {
            dt = diffDays(d);
            break;
        }
        case 'dueDateDiffHuman': {
            const diff = diffDays(d);
            if(diff == 0) {
                dt = 'Today';
            } else if(diff < 0) {
                const abs = Math.abs(diff);
                dt = `${abs} day${abs == 1 ? '' : 's'} ago`;
            } else {
                dt = `in ${diff} days`;
            }
            break;
        }
    }
    return dt;
}

//
// Format Date
// --------------------------------------------------------------------------
export function formatDate(date, type = '') {
    const d = new Date(date);
    return getUSADate(d, type);
}

//
// Format Date Time
// --------------------------------------------------------------------------
export function formatDateTime(date, type = '') {
    const d = parseMySQLDateTime(date);
    return getUSADate(d, type);
}

//
// Current Time
// --------------------------------------------------------------------------
export function currentTime() {
    return new Date().toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true}).replace(/^0+/, '');
}

//
// Find Object in Array by ID and Move it to the top
// --------------------------------------------------------------------------
export function findObjMoveTop(array, id, scroll = true) {
    const selectedObj = array.find(obj => obj.id === id);
    spliceByID(array, id);
    array.unshift(selectedObj);

    scroll && window.scrollTo({
        top: 0,
        left: 0,
        behavior: 'smooth'
    });
    return selectedObj;
}

export function spliceByID(array, id) {
    array.splice(array.findIndex(obj => obj.id === id), 1);
}

//
// Get percentage
// --------------------------------------------------------------------------
export function getPercentage(val, total) {
    return Math.round(((val / total) * 100).toFixed(1));
}

//
// Deep Copy
// --------------------------------------------------------------------------
export function deepCopy(obj) {
    return JSON.parse(JSON.stringify(obj));
}


//
// Wait for condition
// --------------------------------------------------------------------------
// https://stackoverflow.com/a/48178043
export function waitForThis(condition, callback) {
    if (!condition()) {
        lg.log('waiting...');
        window.setTimeout(waitForThis.bind(null, condition, callback), 100);
    } else {
        lg.log('done');
        callback();
    }
}

//
// Change Object Keys in Array of Objects
// --------------------------------------------------------------------------
export function changeArrayObjectKeys(array, cb) {
    array.map(o => {
        const obj = o;
        Object.keys(obj).map(k => {
            let key = cb(k);
            obj[key] = obj[k];
            delete obj[k];
            return key;
        });
        return obj;
    });
    return array;
}

//
// Two levels deep total
// --------------------------------------------------------------------------
export function getNestedArrayTotal(outerArray, innerArray, counterProp, hasProp = false) {
    return outerArray.reduce((outerTotal, obj) =>
        outerTotal + obj[innerArray].reduce((total, obj) => {
            if(hasProp) {
                if(obj[hasProp]) return total + +obj[counterProp];
                else return total
            } else {
                return total + +obj[counterProp];
            }

        }, 0), 0);
}

//
// Get total
// --------------------------------------------------------------------------
export function getArrayTotal(array, counterProp, hasProp = false) {
    return array.reduce((total, obj) => {
        if (hasProp) {
            if (obj[hasProp]) return total + +obj[counterProp];
            else return total;
        } else {
            return total + +obj[counterProp];
        }
    }, 0);
}


// Get number of fields for comparing rows added to nested arrays of objects
// https://stackoverflow.com/questions/50621269/
export function numFields(array) {
    return array
        .reduce(
            (a, b) =>
                a.concat(
                    ...Object.keys(b).map(e =>
                        e === 'fields'
                            ? b[e]
                            : Array.isArray(b[e])
                                ? numFields(b[e], 'fields')
                                : null
                    )
                ),
            []
        )
        .filter(e => e).length;
}

//
// Is Image
// --------------------------------------------------------------------------
export function fileNameIsImage(str) {
    const ext = str.split('.').pop();
    return ['jpg', 'jpeg', 'png', 'gif'].includes(ext);
}

//
// Shorten String
// --------------------------------------------------------------------------
export function shortenString(str, len = 30) {
    if(typeof str !== 'string') return;
    let ellipsis = str.length > len ? '...' : '';
    return str.substring(0, len) + ellipsis;
}


//
// Clear Object Properties
// --------------------------------------------------------------------------
export function clearObject(obj) {
    for (var prop in obj) delete obj[prop];
}

//
// Password check
// --------------------------------------------------------------------------
export const passwordCheck = {
    validate: val => /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d\W_]{6,}$/.test(val),
    message: 'The password must be 6 or more characters, contain one uppercase letter and a number.'
}


//
// Debounce
// https://stackoverflow.com/a/57763036
// --------------------------------------------------------------------------
// export function debounce(callback, wait) {
//     let timeout;
//     return (...args) => {
//         const context = this;
//         clearTimeout(timeout);
//         timeout = setTimeout(() => callback.apply(context, args), wait);
//     };
// }

//
// Random String
// --------------------------------------------------------------------------
// export function randomString() {
//     return (
//         // https://gist.github.com/6174/6062387
//         Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15)
//     );
// }
