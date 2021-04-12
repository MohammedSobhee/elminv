import { getFullName, getDifferenceInDays } from '../functions/utils';

//
// Add Message Type
// --------------------------------------------------------------------------
export function modifySendList(array, type) {
    array.forEach(obj => {
        if(obj.first_name)
            obj.fullName = getFullName(obj.first_name, obj.last_name);
        obj.type = type;
    });

    if(type === 1 && array.length > 1) {
        array.unshift({id: 0, type: 1, class_name: 'All Classes'});
    }
    return array;
    //return modArray;
}


//
// Determine how old a message is in days
// --------------------------------------------------------------------------
export function getDaysOld() {
    const currentDay = (new Date()).getDay();
    // Account for messages sent during weekend on a Monday
    return currentDay === 2 ? -4 : -1;
}

//
// Get recent messages from array
// --------------------------------------------------------------------------
export function numRecentFromList(arr) {
    return arr.filter(obj => getDifferenceInDays(obj.updated_at) >= getDaysOld()).length;
}
